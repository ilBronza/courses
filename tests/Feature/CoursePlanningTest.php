<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\CourseTheme;
use IlBronza\Courses\Models\Date;
use IlBronza\Courses\Models\DateSession;
use IlBronza\Courses\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CoursePlanningTest extends TestCase
{
	private function courseWithTypes() : array
	{
		$course = Course::forceCreate(['name' => 'Formazione lavoratori', 'alias' => 'FL_G', 'common_alias' => 'FL']);
		$theory = $course->dates()->create(['name' => 'Teoria', 'hours' => '8.00', 'sorting_index' => 0]);
		$practice = $course->dates()->create(['name' => 'Pratica', 'hours' => '4.00', 'sorting_index' => 1]);
		return [$course, $theory, $practice];
	}

	private function program(Date $theory, Date $practice) : array
	{
		return [
			['date_id' => $theory->getKey(), 'name' => 'Teoria 1', 'hours' => '4.00', 'description' => 'Introduzione'],
			['date_id' => $theory->getKey(), 'name' => 'Teoria 2', 'hours' => '4.00'],
			['date_id' => $practice->getKey(), 'name' => 'Esercitazione', 'hours' => '4.00'],
		];
	}

	private function plannedTheme() : CourseTheme
	{
		[$course, $theory, $practice] = $this->courseWithTypes();
		return $course->themes()->create(['name' => 'Farmacia', 'program' => $this->program($theory, $practice)]);
	}

	public function test_date_types_can_be_created_updated_and_deleted_through_crud() : void
	{
		$course = Course::forceCreate(['name' => 'Corso']);
		$payload = ['course_id' => $course->getKey(), 'name' => 'Teoria', 'hours' => '8.50', 'sorting_index' => 0];
		$this->post(route('ibCourses.dates.store'), $payload)->assertRedirect();
		$type = Date::firstOrFail();
		$this->assertSame('8.50', $type->hours);
		$this->assertTrue($type->course->is($course));
		$this->put($type->getUpdateUrl(), array_replace($payload, ['hours' => '8.00']))->assertRedirect();
		$this->assertSame('8.00', $type->fresh()->hours);
		foreach(['0', '-1', '1.001', '1000'] as $hours)
			$this->putJson($type->getUpdateUrl(), array_replace($payload, ['hours' => $hours]))
				->assertUnprocessable()->assertJsonValidationErrors('hours');
		$this->delete($type->getDeleteUrl())->assertRedirect();
		$this->assertSoftDeleted($type);
	}

	public function test_theme_program_can_split_a_type_without_changing_its_total() : void
	{
		[$course, $theory, $practice] = $this->courseWithTypes();
		$program = $this->program($theory, $practice);
		$this->post(route('ibCourses.courseThemes.store'), [
			'course_id' => $course->getKey(), 'name' => 'Farmacia',
			'program' => [2 => $program[0], 5 => $program[1], 9 => $program[2]],
		])->assertRedirect();
		$theme = CourseTheme::firstOrFail();
		$this->assertCount(3, $theme->program);
		$this->assertSame([0, 1, 2], array_keys($theme->program));
		$this->assertSame('Programma completo', $theme->program_status);
	}

	public function test_program_rejects_missing_excess_and_swapped_hours_without_partial_writes() : void
	{
		[$course, $theory, $practice] = $this->courseWithTypes();
		$program = $this->program($theory, $practice);
		$wrongTotals = $program;
		$wrongTotals[1]['hours'] = '5';
		$wrongTotals[2]['hours'] = '3'; // Still 12 overall, but wrong for both types.
		$excess = $program;
		$excess[2]['hours'] = '5';
		$missing = array_slice($program, 0, 2);

		foreach([$wrongTotals, $excess, $missing] as $invalid)
			$this->postJson(route('ibCourses.courseThemes.store'), [
				'course_id' => $course->getKey(), 'name' => 'Farmacia', 'program' => $invalid,
			])->assertUnprocessable()->assertJsonValidationErrors('program');
		$this->assertDatabaseCount('courses__course_themes', 0);
	}

	public function test_program_rejects_types_from_other_courses_and_invalid_rows() : void
	{
		[$course, $theory, $practice] = $this->courseWithTypes();
		[, $otherType] = $this->courseWithTypes();
		$program = $this->program($theory, $practice);
		$program[0]['date_id'] = $otherType->getKey();
		$this->postJson(route('ibCourses.courseThemes.store'), [
			'course_id' => $course->getKey(), 'name' => 'Farmacia', 'program' => $program,
		])->assertUnprocessable()->assertJsonValidationErrors('program.0.date_id');

		foreach([['hours' => '-4'], ['hours' => '4.001'], ['name' => ''], ['extra' => 'unexpected']] as $invalid)
		{
			$program = $this->program($theory, $practice);
			$program[0] = array_replace($program[0], $invalid);
			$this->postJson(route('ibCourses.courseThemes.store'), [
				'course_id' => $course->getKey(), 'name' => 'Farmacia', 'program' => $program,
			])->assertUnprocessable();
		}
	}

	public function test_fractional_hours_are_compared_exactly() : void
	{
		[$course, $theory, $practice] = $this->courseWithTypes();
		$program = [
			['date_id' => $theory->getKey(), 'name' => 'A', 'hours' => '0.10'],
			['date_id' => $theory->getKey(), 'name' => 'B', 'hours' => '0.20'],
			['date_id' => $theory->getKey(), 'name' => 'C', 'hours' => '7.70'],
			['date_id' => $practice->getKey(), 'name' => 'D', 'hours' => '4'],
		];
		$theme = $course->themes()->create(['name' => 'Decimali', 'program' => $program]);
		$this->assertCount(4, $theme->fresh()->program);
	}

	public function test_edition_creation_copies_program_once_and_keeps_existing_editions_independent() : void
	{
		$theme = $this->plannedTheme();
		$this->post(route('ibCourses.courseSessions.store'), [
			'course_theme_id' => $theme->getKey(), 'name' => 'Ottobre',
		])->assertRedirect();
		$edition = CourseSession::firstOrFail();
		$this->assertTrue($edition->course->is($theme->course));
		$this->assertTrue($edition->courseTheme->is($theme));
		$this->assertCount(3, $edition->dateSessions);
		$this->assertSame(['Teoria', 'Teoria', 'Pratica'], $edition->dateSessions->pluck('type_name')->all());
		$this->assertSame('Introduzione', $edition->dateSessions->first()->description);
		$this->assertNull($edition->dateSessions->first()->starts_at);

		$program = $theme->program;
		$program[0]['hours'] = '3.5';
		$program[1]['hours'] = '4.5';
		$program[0]['name'] = 'Nuova introduzione';
		$this->put($theme->getUpdateUrl(), [
			'course_id' => $theme->course_id, 'name' => 'Farmacia', 'program' => $program,
		])->assertRedirect();
		$edition->update(['name' => 'Ottobre aggiornato']);
		$this->assertCount(3, $edition->fresh()->dateSessions);
		$this->assertSame('4.00', $edition->fresh()->dateSessions->first()->hours);
		$this->assertSame('Teoria 1', $edition->fresh()->dateSessions->first()->name);

		$newEdition = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Novembre']);
		$this->assertSame('3.50', $newEdition->dateSessions->first()->hours);
		$this->assertSame('Nuova introduzione', $newEdition->dateSessions->first()->name);
	}

	public function test_drafts_inactive_themes_and_invalid_date_ranges_cannot_create_editions() : void
	{
		[$course] = $this->courseWithTypes();
		$draft = $course->themes()->create(['name' => 'Bozza']);
		$this->postJson(route('ibCourses.courseSessions.store'), [
			'course_theme_id' => $draft->getKey(), 'name' => 'Incompleta',
		])->assertUnprocessable()->assertJsonValidationErrors('course_theme_id');
		$theme = $this->plannedTheme();
		$this->postJson(route('ibCourses.courseSessions.store'), [
			'course_theme_id' => $theme->getKey(), 'name' => 'Date errate',
			'desired_start_date' => '2026-10-20', 'desired_end_date' => '2026-10-10',
		])->assertUnprocessable()->assertJsonValidationErrors('desired_end_date');
		$theme->delete();
		$this->postJson(route('ibCourses.courseSessions.store'), [
			'course_theme_id' => $theme->getKey(), 'name' => 'Eliminata',
		])->assertUnprocessable()->assertJsonValidationErrors('course_theme_id');
		$this->assertDatabaseCount('courses__course_sessions', 0);
		$this->assertDatabaseCount('courses__date_sessions', 0);
	}

	public function test_lesson_can_be_scheduled_and_unscheduled_without_changing_required_hours() : void
	{
		$theme = $this->plannedTheme();
		$edition = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre']);
		$lesson = $edition->dateSessions->first();
		$this->put($lesson->getUpdateUrl(), [
			'name' => 'Teoria 1', 'starts_at' => '2026-10-12T09:00', 'hours' => '99',
		])->assertRedirect();
		$this->assertSame('2026-10-12 09:00', $lesson->fresh()->starts_at->format('Y-m-d H:i'));
		$this->assertSame('4.00', $lesson->fresh()->hours);
		$this->put($lesson->getUpdateUrl(), ['name' => 'Teoria 1', 'starts_at' => null])->assertRedirect();
		$this->assertNull($lesson->fresh()->starts_at);
		$this->putJson($lesson->getUpdateUrl(), ['name' => 'Teoria 1', 'starts_at' => 'invalid'])->assertUnprocessable();

		$this->expectException(ValidationException::class);
		$lesson->update(['hours' => '7']);
	}

	public function test_type_changes_cannot_invalidate_saved_programs() : void
	{
		$theme = $this->plannedTheme();
		$type = $theme->course->dates->first();
		$this->putJson($type->getUpdateUrl(), [
			'course_id' => $theme->course_id, 'name' => 'Teoria', 'hours' => 9, 'sorting_index' => 0,
		])->assertUnprocessable()->assertJsonValidationErrors('hours');
		$this->deleteJson($type->getDeleteUrl())->assertUnprocessable();
		$this->postJson(route('ibCourses.dates.store'), [
			'course_id' => $theme->course_id, 'name' => 'Esame', 'hours' => 1, 'sorting_index' => 2,
		])->assertUnprocessable();
		$this->assertSame('8.00', $type->fresh()->hours);
		$this->assertCount(2, $theme->course->dates);
	}

	public function test_deleting_edition_removes_its_dates_but_keeps_template_and_other_editions() : void
	{
		$theme = $this->plannedTheme();
		$first = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre']);
		$second = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Novembre']);
		$firstLesson = $first->dateSessions->first();
		$this->delete($first->getDeleteUrl())->assertRedirect();
		$this->assertSoftDeleted($first);
		$this->assertSoftDeleted($firstLesson);
		$this->assertSame(0, $first->dateSessions()->count());
		$this->assertSame(3, $second->dateSessions()->count());
		$this->assertNotNull($theme->fresh());
		$this->delete($theme->course->getDeleteUrl())->assertRedirect();
		$this->assertSame(0, DateSession::count());
	}

	public function test_failed_generation_rolls_back_the_whole_edition() : void
	{
		$theme = $this->plannedTheme();
		$count = 0;
		Event::listen('eloquent.creating: ' . DateSession::class, function () use (&$count)
		{
			if(++$count === 2)
				throw new RuntimeException('Failed lesson');
		});
		try {
			CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Errore']);
			$this->fail('Expected creation to fail');
		} catch (RuntimeException $exception) {
			$this->assertSame('Failed lesson', $exception->getMessage());
		}
		$this->assertDatabaseCount('courses__course_sessions', 0);
		$this->assertDatabaseCount('courses__date_sessions', 0);
	}

	public function test_planning_pages_and_related_links_are_available() : void
	{
		$this->withoutExceptionHandling();
		$theme = $this->plannedTheme();
		$edition = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Ottobre']);
		$type = $theme->course->dates->first();
		$lesson = $edition->dateSessions->first();
		$lesson->update(['starts_at' => '2026-10-12 09:00:00']);

		foreach([$type, $edition, $lesson] as $model)
		{
			$this->get($model->getIndexUrl())->assertOk();
			$this->get($model->getIndexUrl(), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
			$this->get($model->getShowUrl())->assertOk();
			$this->get($model->getEditUrl())->assertOk();
		}
		$this->get($type->getCreateByRelatedUrl($theme->course))->assertOk();
		$this->get($edition->getCreateByRelatedUrl($theme))->assertOk();
		$this->get($theme->getEditUrl())->assertOk()->assertSee('Teoria 1');
		$this->get($theme->getShowUrl())->assertOk()->assertSee('Nuova edizione');
		$this->get($theme->course->getShowUrl())->assertOk()->assertSee('Nuova tipologia di data');
		$this->get($edition->getShowUrl())->assertOk()->assertSee('Date delle edizioni');
		$this->get($edition->getShowUrl() . '?model=dateSessions', ['X-Requested-With' => 'XMLHttpRequest'])
			->assertOk()->assertSee('Teoria 1');
		$this->get($theme->course->getShowUrl() . '?model=dates', ['X-Requested-With' => 'XMLHttpRequest'])
			->assertOk()->assertSee('Pratica');
	}

	public function test_invalid_program_update_preserves_saved_plan_and_empty_program_uses_course_defaults_in_form() : void
	{
		$theme = $this->plannedTheme();
		$original = $theme->program;
		$invalid = $original;
		$invalid[0]['hours'] = '5';
		$this->putJson($theme->getUpdateUrl(), [
			'course_id' => $theme->course_id, 'name' => 'Changed', 'program' => $invalid,
		])->assertUnprocessable()->assertJsonValidationErrors('program');
		$this->assertSame($original, $theme->fresh()->program);
		$this->assertSame('Farmacia', $theme->fresh()->name);

		$draft = $theme->course->themes()->create(['name' => 'Bozza']);
		$response = $this->get($draft->getEditUrl())->assertOk();
		$response->assertSee('Teoria')->assertSee('Pratica')->assertSee('value="8.00"', false)->assertSee('value="4.00"', false);
		$this->assertNull($draft->fresh()->program);
	}
}
