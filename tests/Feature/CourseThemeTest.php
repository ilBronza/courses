<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseTheme;
use IlBronza\Courses\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseThemeTest extends TestCase
{
	private function createCourse(string $name = 'Formazione lavoratori') : Course
	{
		return Course::forceCreate(['name' => $name, 'alias' => 'FL_S', 'common_alias' => 'FL']);
	}

	public function test_store_preserves_ateco_strings_and_course_relationship() : void
	{
		$course = $this->createCourse();

		$this->post(route('ibCourses.courseThemes.store'), [
			'course_id' => $course->getKey(),
			'name' => 'Farmacia',
			'description' => 'Tematica per farmacie',
			'ateco' => [2 => ['code' => '01.11.00'], 5 => ['code' => '02.10.00'], 8 => ['code' => '']],
		])->assertRedirect();

		$theme = CourseTheme::firstOrFail();
		$this->assertTrue(Str::isUuid($theme->getKey()));
		$this->assertTrue($theme->course->is($course));
		$this->assertTrue($course->themes->first()->is($theme));
		$this->assertSame([['code' => '01.11.00'], ['code' => '02.10.00']], $theme->ateco);
		$this->assertSame('01.11.00, 02.10.00', $theme->ateco_codes);
		$this->assertSame($theme->ateco, json_decode(DB::table($theme->getTable())->value('ateco'), true));
	}

	public function test_store_accepts_a_theme_without_ateco() : void
	{
		$course = $this->createCourse();
		$this->post(route('ibCourses.courseThemes.store'), [
			'course_id' => $course->getKey(), 'name' => 'Parafarmacia',
		])->assertRedirect();

		$this->assertSame([], CourseTheme::firstOrFail()->ateco);
	}

	public function test_store_rejects_invalid_codes_and_invalid_course() : void
	{
		$course = $this->createCourse();
		$base = ['course_id' => $course->getKey(), 'name' => 'Farmacia'];

		foreach([
			[['ateco' => '01.11.00'], 'ateco'],
			[['ateco' => ['01.11.00']], 'ateco.0'],
			[['ateco' => [['code' => 11100]]], 'ateco.0.code'],
			[['ateco' => [['code' => '01.11.00'], ['code' => '01.11.00']]], 'ateco.0.code'],
			[['ateco' => [['code' => '01.11.00', 'unexpected' => true]]], 'ateco.0'],
			[['course_id' => (string) Str::uuid()], 'course_id'],
			[['name' => ''], 'name'],
		] as [$input, $error])
			$this->postJson(route('ibCourses.courseThemes.store'), array_replace($base, $input))
				->assertUnprocessable()->assertJsonValidationErrors($error);

		$this->assertDatabaseCount('courses__course_themes', 0);
	}

	public function test_update_changes_and_clears_all_ateco_rows() : void
	{
		$course = $this->createCourse();
		$theme = $course->themes()->create(['name' => 'Farmacia', 'ateco' => [['code' => '01.11.00']]]);
		$base = ['course_id' => $course->getKey(), 'name' => 'Parafarmacia'];

		$this->put($theme->getUpdateUrl(), $base + ['ateco' => [['code' => '02.10.00']]])->assertRedirect();
		$this->assertSame([['code' => '02.10.00']], $theme->fresh()->ateco);
		$this->assertSame('Parafarmacia', $theme->fresh()->name);

		$this->put($theme->getUpdateUrl(), $base)->assertRedirect();
		$this->assertSame([], $theme->fresh()->ateco);
	}

	public function test_deleted_course_cannot_receive_new_themes() : void
	{
		$course = $this->createCourse();
		$course->delete();
		$this->postJson(route('ibCourses.courseThemes.store'), [
			'course_id' => $course->getKey(), 'name' => 'Farmacia',
		])->assertUnprocessable()->assertJsonValidationErrors('course_id');
	}

	public function test_theme_can_be_deleted_without_deleting_its_course() : void
	{
		$course = $this->createCourse();
		$theme = $course->themes()->create(['name' => 'Farmacia']);
		$this->delete($theme->getDeleteUrl())->assertRedirect();
		$this->assertSoftDeleted('courses__course_themes', ['id' => $theme->getKey()]);
		$this->assertNotNull($course->fresh());
	}

	public function test_deleting_course_through_crud_deletes_only_its_themes() : void
	{
		$course = $this->createCourse();
		$theme = $course->themes()->create(['name' => 'Farmacia']);
		$other = $this->createCourse('Altro corso')->themes()->create(['name' => 'Altro settore']);

		$this->delete($course->getDeleteUrl())->assertRedirect();
		$this->assertSoftDeleted('courses__course_themes', ['id' => $theme->getKey()]);
		$this->assertNotNull($other->fresh());
	}

	public function test_pages_and_related_creation_are_available() : void
	{
		$this->withoutExceptionHandling();
		$course = $this->createCourse();
		$theme = $course->themes()->create(['name' => 'Farmacia', 'ateco' => [['code' => '01.11.00']]]);

		$this->get(route('ibCourses.courseThemes.index'))->assertOk();
		$this->get($theme->getShowUrl())->assertOk()->assertSee('01.11.00');
		$this->get($theme->getEditUrl())->assertOk()->assertSee('01.11.00');
		$url = $theme->getCreateByRelatedUrl($course);
		$this->assertStringContainsString('course_id=' . $course->getKey(), $url);
		$this->get($url)->assertOk()->assertSee('Codici ATECO');
		$this->get($course->getShowUrl())->assertOk()->assertSee('Nuova tematica');
	}
}
