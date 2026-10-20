<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Models\Responsibility;
use IlBronza\Courses\Tests\TestCase;
use IlBronza\Operators\Models\Operator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OperatorCourseSessionIndexTest extends TestCase
{
	protected function defineEnvironment($app)
	{
		parent::defineEnvironment($app);
		$app['config']->set('operators.models.operator', [
			'class' => CoursesIndexTestOperator::class,
			'table' => 'operators__operators',
		]);
	}

	protected function setUp() : void
	{
		parent::setUp();
		Schema::create(config('operators.models.operator.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->timestamps();
			$table->softDeletes();
		});
		foreach([
			'2026_07_24_000004_create_courses__responsibilities_table.php',
			'2026_08_31_000020_add_archived_at_to_courses__responsibilities_table.php',
			'2026_10_19_000024_create_courses__operator_course_sessions_table.php',
			'2026_10_20_000025_add_client_id_to_courses__operator_course_sessions_table.php',
		] as $filename)
			(require __DIR__ . '/../../database/migrations/' . $filename)->up();
	}

	private function responsibility(string $id, ?string $description = null) : Responsibility
	{
		return Responsibility::forceCreate(['id' => $id, 'description' => $description]);
	}

	private function participation(Responsibility $responsibility, string $createdAt) : OperatorCourseSession
	{
		$course = Course::forceCreate([
			'name' => 'Corso ' . $responsibility->getKey(),
			'alias' => $responsibility->getKey(),
			'common_alias' => $responsibility->getKey(),
		]);
		$type = $course->dates()->create(['name' => 'Teoria', 'hours' => '4.00']);
		$theme = $course->themes()->create(['name' => 'Farmacia', 'program' => [
			['date_id' => $type->getKey(), 'name' => 'Lezione', 'hours' => '4.00'],
		]]);
		$edition = CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => 'Edizione ' . $responsibility->getKey()]);
		$operatorId = (string) Str::uuid();
		DB::table(config('operators.models.operator.table'))->insert(['id' => $operatorId]);
		$participation = $edition->operatorCourseSessions()->create([
			'operator_id' => $operatorId, 'status' => 'completato', 'result' => 'superato',
		]);
		$participation->forceFill(['created_at' => $createdAt])->save();

		return $participation;
	}

	private function indexIds(string $route, array $parameters = []) : array
	{
		$response = $this->getJson(route('ibCourses.operatorCourseSessions.' . $route, $parameters), [
			'X-Requested-With' => 'XMLHttpRequest',
		])->assertOk();

		return array_map(fn ($row) => collect($row)->first(fn ($cell) => is_string($cell) && Str::isUuid($cell)), $response->json('data'));
	}

	public function test_all_and_recent_indexes_include_only_their_participations_in_newest_first_order() : void
	{
		$this->travelTo(now()->setDate(2026, 10, 20)->setTime(12, 0));
		$responsibility = $this->responsibility('FL', 'Lavoratori');
		$old = $this->participation($responsibility, '2025-10-20 11:59:59');
		$boundary = $this->participation($responsibility, '2025-10-20 12:00:00');
		$recent = $this->participation($responsibility, '2026-10-20 10:00:00');
		$deleted = $this->participation($responsibility, '2026-10-20 11:00:00');
		$deleted->delete();

		$this->assertSame([$recent->getKey(), $boundary->getKey(), $old->getKey()], $this->indexIds('index'));
		$this->assertSame([$recent->getKey(), $boundary->getKey()], $this->indexIds('recent'));
		$this->travelBack();
	}

	public function test_responsibility_index_filters_through_the_course_and_keeps_historical_parents() : void
	{
		$workers = $this->responsibility('FL', 'Lavoratori');
		$firstAid = $this->responsibility('PS', 'Primo soccorso');
		$participation = $this->participation($workers, '2026-01-01 12:00:00');
		$other = $this->participation($firstAid, '2026-01-01 12:00:00');
		$participation->courseSession->course->delete();
		$participation->courseSession->delete();
		$participation->operator->delete();

		$this->assertSame([$participation->getKey()], $this->indexIds('byResponsibility', ['responsibility' => 'FL']));
		$this->assertSame([$other->getKey()], $this->indexIds('byResponsibility', ['responsibility' => 'PS']));
		$this->assertSame([], $this->indexIds('byResponsibility', ['responsibility' => 'missing']));
	}

	public function test_menu_exposes_recent_all_and_one_type_for_each_responsibility() : void
	{
		$this->responsibility('PS', 'Primo soccorso');
		$this->responsibility('FL', 'Lavoratori');
		$this->responsibility('OTHER');
		$this->responsibility('DELETED', 'Eliminata')->delete();
		app('courses')->manageMenuButtons();

		$courses = app('menu')->getButtonByName('settings')->getChildren()->firstWhere('name', 'coursesManager');
		$button = $courses->getChildren()->firstWhere('name', 'operatorCourseSessions');
		$this->assertSame('coursesManager', $button->getParent()->getName());
		$this->assertSame(['Recenti', 'Tutti', 'Per tipo'], $button->getChildren()->map->getText()->all());
		$this->assertSame(route('ibCourses.operatorCourseSessions.recent'), $button->getChildren()[0]->getHref());
		$this->assertSame(route('ibCourses.operatorCourseSessions.index'), $button->getChildren()[1]->getHref());
		$types = $button->getChildren()[2]->getChildren();
		$this->assertSame(['OTHER', 'Lavoratori', 'Primo soccorso'], $types->map->getText()->all());
		foreach(['OTHER', 'FL', 'PS'] as $index => $id)
			$this->assertSame(route('ibCourses.operatorCourseSessions.byResponsibility', ['responsibility' => $id]), $types[$index]->getHref());
	}

	public function test_index_pages_render_translated_participation_fields_and_json_values() : void
	{
		$this->withoutExceptionHandling();
		$participation = $this->participation($this->responsibility('FL', 'Lavoratori'), '2026-10-20 10:00:00');
		foreach(['index', 'recent', 'byResponsibility'] as $route)
			$this->get(route('ibCourses.operatorCourseSessions.' . $route, $route === 'byResponsibility' ? ['responsibility' => 'FL'] : []))
				->assertOk()->assertSee('Operatore')->assertSee('Edizione')->assertSee('Stato')->assertSee('Esito')
				->assertSee('Scarica PDF')->assertSee('Invia attestati')
				->assertSee('data-course-certificate-action="download"', false)
				->assertSee('data-course-certificate-action="send"', false)
				->assertDontSee('data-redirectsubmit=', false)
				->assertSee('selectRow');

		$response = $this->getJson(route('ibCourses.operatorCourseSessions.index'), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
		$response->assertSee('Operatore prova')->assertSee('Corso FL')->assertSee('Lavoratori')->assertSee('Edizione FL')
			->assertSee('completato')->assertSee('superato')->assertSee($participation->getKey());
	}
}

class CoursesIndexTestOperator extends Operator
{
	protected $with = [];

	public function getName() : ?string
	{
		return 'Operatore prova';
	}
}
