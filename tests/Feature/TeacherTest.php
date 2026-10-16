<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\CourseTheme;
use IlBronza\Courses\Models\Teacher;
use IlBronza\Courses\Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherTest extends TestCase
{
	private function plannedTheme() : CourseTheme
	{
		$course = Course::forceCreate(['name' => 'Formazione lavoratori']);
		$type = $course->dates()->create(['name' => 'Teoria', 'hours' => '4.00']);
		return $course->themes()->create(['name' => 'Farmacia', 'program' => [
			['date_id' => $type->getKey(), 'name' => 'Lezione', 'hours' => '4.00'],
		]]);
	}

	public function test_teacher_crud_validates_and_saves_contact_details() : void
	{
		$payload = ['name' => 'Anna Rossi', 'email' => 'anna@example.test', 'phone' => '+39 0123 456789', 'note' => 'Docente di teoria'];
		$this->post(route('ibCourses.teachers.store'), $payload)->assertRedirect();
		$teacher = Teacher::firstOrFail();
		$this->assertTrue(Str::isUuid($teacher->getKey()));
		$this->assertSame($payload, $teacher->only(array_keys($payload)));
		$this->put($teacher->getUpdateUrl(), ['name' => 'Anna Bianchi', 'email' => null, 'phone' => null, 'note' => null])->assertRedirect();
		$this->assertSame('Anna Bianchi', $teacher->fresh()->name);
		$this->assertNull($teacher->fresh()->email);
		foreach([
			[['name' => ''], 'name'],
			[['name' => 'Anna', 'email' => 'invalid'], 'email'],
			[['name' => 'Anna', 'phone' => str_repeat('1', 51)], 'phone'],
		] as [$input, $error])
			$this->postJson(route('ibCourses.teachers.store'), $input)->assertUnprocessable()->assertJsonValidationErrors($error);
		$this->assertDatabaseCount('courses__teachers', 1);
		$this->delete($teacher->getDeleteUrl())->assertRedirect();
		$this->assertSoftDeleted($teacher);
	}

	public function test_editions_have_independent_multiple_teachers_and_teachers_can_be_reused() : void
	{
		$theme = $this->plannedTheme();
		$anna = Teacher::create(['name' => 'Anna Rossi']);
		$luca = Teacher::create(['name' => 'Luca Verdi']);
		$payload = ['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre', 'teachers' => [$anna->getKey(), $luca->getKey()]];
		$this->post(route('ibCourses.courseSessions.store'), $payload)->assertRedirect();
		$october = CourseSession::firstOrFail();
		$this->assertSame([$anna->getKey(), $luca->getKey()], $october->teachers->modelKeys());
		$this->assertSame('Anna Rossi, Luca Verdi', $october->teacher_names);
		$this->assertCount(1, $october->dateSessions);
		$this->post(route('ibCourses.courseSessions.store'), array_replace($payload, ['name' => 'Novembre', 'teachers' => [$luca->getKey()]]))->assertRedirect();
		$november = CourseSession::where('name', 'Novembre')->firstOrFail();
		$this->assertSame([$luca->getKey()], $november->teachers->modelKeys());
		$this->assertCount(2, $luca->courseSessions);
		$this->assertCount(1, $anna->courseSessions);
		$this->assertCount(2, $october->fresh()->teachers);
		$this->assertDatabaseCount('courses__course_session_teacher', 3);
	}

	public function test_edition_update_replaces_and_clears_teacher_assignments() : void
	{
		$edition = CourseSession::create(['course_theme_id' => $this->plannedTheme()->getKey(), 'name' => 'Ottobre']);
		$anna = Teacher::create(['name' => 'Anna Rossi']);
		$luca = Teacher::create(['name' => 'Luca Verdi']);
		$edition->teachers()->attach($anna);
		$this->put($edition->getUpdateUrl(), ['name' => 'Ottobre', 'teachers' => [$luca->getKey()]])->assertRedirect();
		$this->assertSame([$luca->getKey()], $edition->fresh()->teachers->modelKeys());
		$this->put($edition->getUpdateUrl(), ['name' => 'Ottobre', 'teachers' => []])->assertRedirect();
		$this->assertCount(0, $edition->fresh()->teachers);
		$edition->teachers()->attach($anna);
		$this->put($edition->getUpdateUrl(), ['name' => 'Ottobre senza docenti'])->assertRedirect();
		$this->assertCount(0, $edition->fresh()->teachers);
		$this->assertDatabaseCount('courses__teachers', 2);
	}

	public function test_invalid_assignments_do_not_create_editions_or_modify_existing_data() : void
	{
		$theme = $this->plannedTheme();
		$anna = Teacher::create(['name' => 'Anna Rossi']);
		$deleted = Teacher::create(['name' => 'Eliminato']);
		$deleted->delete();
		$edition = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre']);
		$edition->teachers()->attach($anna);
		foreach([
			'bad-shape', ['invalid'], [(string) Str::uuid()], [$deleted->getKey()],
			[$anna->getKey(), $anna->getKey()], [['id' => $anna->getKey()]],
		] as $teachers)
		{
			$this->postJson(route('ibCourses.courseSessions.store'), [
				'course_theme_id' => $theme->getKey(), 'name' => 'Non salvare', 'teachers' => $teachers,
			])->assertUnprocessable();
			$this->putJson($edition->getUpdateUrl(), ['name' => 'Non cambiare', 'teachers' => $teachers])->assertUnprocessable();
		}
		$this->assertSame('Ottobre', $edition->fresh()->name);
		$this->assertSame([$anna->getKey()], $edition->fresh()->teachers->modelKeys());
		$this->assertDatabaseCount('courses__course_sessions', 1);
		$this->assertDatabaseCount('courses__date_sessions', 1);
		$this->assertDatabaseCount('courses__course_session_teacher', 1);
	}

	public function test_deleted_teachers_remain_in_history_but_cannot_receive_new_assignments() : void
	{
		$theme = $this->plannedTheme();
		$edition = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre']);
		$teacher = Teacher::create(['name' => 'Anna Rossi']);
		$edition->teachers()->attach($teacher);
		$this->delete($teacher->getDeleteUrl())->assertRedirect();
		$this->assertCount(1, $edition->fresh()->teachers);
		$this->get($edition->getShowUrl())->assertOk()->assertSee('Anna Rossi');
		$this->get($teacher->getShowUrl())->assertOk()->assertSee('Anna Rossi');
		$this->get($edition->getEditUrl())->assertOk()->assertSee('Anna Rossi (eliminato)');
		$this->get(route('ibCourses.courseSessions.create'))->assertOk()->assertDontSee('Anna Rossi');
		$this->put($edition->getUpdateUrl(), ['name' => 'Ottobre aggiornato', 'teachers' => [$teacher->getKey()]])->assertRedirect();
		$this->assertCount(1, $edition->fresh()->teachers);
		$this->put($edition->getUpdateUrl(), ['name' => 'Ottobre aggiornato'])->assertRedirect();
		$this->assertCount(0, $edition->fresh()->teachers);
		$this->putJson($edition->getUpdateUrl(), ['name' => 'Ottobre', 'teachers' => [$teacher->getKey()]])->assertUnprocessable();
	}

	public function test_deleting_an_edition_keeps_teachers_and_other_assignments() : void
	{
		$theme = $this->plannedTheme();
		$october = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre']);
		$november = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Novembre']);
		$teacher = Teacher::create(['name' => 'Anna Rossi']);
		$teacher->courseSessions()->attach([$october->getKey(), $november->getKey()]);
		$this->delete($october->getDeleteUrl())->assertRedirect();
		$this->assertNotNull($teacher->fresh());
		$this->assertSame([$november->getKey()], $teacher->fresh()->courseSessions->modelKeys());
		$this->assertCount(1, $november->fresh()->teachers);
	}

	public function test_teacher_pages_and_edition_selections_show_the_assigned_teachers() : void
	{
		$this->withoutExceptionHandling();
		$edition = CourseSession::create(['course_theme_id' => $this->plannedTheme()->getKey(), 'name' => 'Ottobre']);
		$teacher = Teacher::create(['name' => 'Anna Rossi']);
		$edition->teachers()->attach($teacher);
		$this->get($teacher->getIndexUrl())->assertOk();
		$this->get($teacher->getIndexUrl(), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSee('Anna Rossi');
		$this->get($teacher->getCreateUrl())->assertOk()->assertSee('Telefono');
		$this->get($teacher->getEditUrl())->assertOk()->assertSee('Anna Rossi');
		$this->get($teacher->getShowUrl())->assertOk()->assertSee('Edizioni');
		$this->get($teacher->getShowUrl() . '?model=courseSessions', ['X-Requested-With' => 'XMLHttpRequest'])
			->assertOk()->assertSee('Ottobre');
		$response = $this->get($edition->getEditUrl())->assertOk()->assertSee('Docenti');
		$this->assertMatchesRegularExpression('/<option[^>]*value="' . $teacher->getKey() . '"[^>]*selected/', $response->getContent());
		$this->get($edition->getShowUrl())->assertOk()->assertSee($teacher->getShowUrl(), false);
		$this->get($edition->getIndexUrl(), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSee('Anna Rossi');
	}

	public function test_failed_assignment_rolls_back_the_edition_and_its_lessons() : void
	{
		$this->withoutExceptionHandling();
		$theme = $this->plannedTheme();
		$teacher = Teacher::create(['name' => 'Anna Rossi']);
		DB::unprepared("CREATE TRIGGER fail_teacher_assignment BEFORE INSERT ON courses__course_session_teacher BEGIN SELECT RAISE(ABORT, 'Failed assignment'); END");
		try {
			$this->post(route('ibCourses.courseSessions.store'), [
				'course_theme_id' => $theme->getKey(), 'name' => 'Ottobre', 'teachers' => [$teacher->getKey()],
			]);
			$this->fail('Expected the assignment to fail');
		} catch (QueryException $exception) {
			$this->assertStringContainsString('Failed assignment', $exception->getMessage());
		}
		$this->assertDatabaseCount('courses__course_sessions', 0);
		$this->assertDatabaseCount('courses__date_sessions', 0);
		$this->assertDatabaseCount('courses__course_session_teacher', 0);
	}

	public function test_failed_assignment_update_preserves_the_edition_and_its_previous_teachers() : void
	{
		$this->withoutExceptionHandling();
		$edition = CourseSession::create(['course_theme_id' => $this->plannedTheme()->getKey(), 'name' => 'Ottobre']);
		$anna = Teacher::create(['name' => 'Anna Rossi']);
		$luca = Teacher::create(['name' => 'Luca Verdi']);
		$edition->teachers()->attach($anna);
		DB::unprepared("CREATE TRIGGER fail_teacher_assignment BEFORE INSERT ON courses__course_session_teacher BEGIN SELECT RAISE(ABORT, 'Failed assignment'); END");
		try {
			$this->put($edition->getUpdateUrl(), ['name' => 'Non salvare', 'teachers' => [$luca->getKey()]]);
			$this->fail('Expected the assignment to fail');
		} catch (QueryException $exception) {
			$this->assertStringContainsString('Failed assignment', $exception->getMessage());
		}
		$this->assertSame('Ottobre', $edition->fresh()->name);
		$this->assertSame([$anna->getKey()], $edition->fresh()->teachers->modelKeys());
	}

	public function test_inline_updates_cannot_bypass_teacher_validation() : void
	{
		$edition = CourseSession::create(['course_theme_id' => $this->plannedTheme()->getKey(), 'name' => 'Ottobre']);
		$anna = Teacher::create(['name' => 'Anna Rossi']);
		$deleted = Teacher::create(['name' => 'Eliminato']);
		$deleted->delete();
		$edition->teachers()->attach($anna);
		foreach([[(string) Str::uuid()], [$deleted->getKey()], [$anna->getKey(), $anna->getKey()]] as $teachers)
			$this->putJson($edition->getUpdateUrl(), ['ib-editor' => true, 'field' => 'teachers', 'value' => $teachers])
				->assertUnprocessable();
		$this->assertSame([$anna->getKey()], $edition->fresh()->teachers->modelKeys());
	}
}
