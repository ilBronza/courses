<?php

namespace IlBronza\Courses\Tests\Feature;

use Carbon\Carbon;
use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Tests\TestCase;
use IlBronza\Courses\Traits\ExternalTraits\Models\OperatorUsesCoursesTrait;
use IlBronza\Operators\Models\Operator;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OperatorCourseSessionTest extends TestCase
{
	protected function defineEnvironment($app)
	{
		parent::defineEnvironment($app);
		$app['config']->set('operators.models.operator', [
			'class' => CoursesTestOperator::class,
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
			'2026_10_19_000024_create_courses__operator_course_sessions_table.php',
			'2026_10_20_000025_add_client_id_to_courses__operator_course_sessions_table.php',
		] as $filename)
			(require __DIR__ . '/../../database/migrations/' . $filename)->up();
	}

	private function operator() : CoursesTestOperator
	{
		$id = (string) Str::uuid();
		DB::table(config('operators.models.operator.table'))->insert(['id' => $id]);
		return CoursesTestOperator::findOrFail($id);
	}

	private function edition(string $name = 'Ottobre') : CourseSession
	{
		$course = Course::forceCreate(['name' => 'Formazione lavoratori']);
		$type = $course->dates()->create(['name' => 'Teoria', 'hours' => '4.00']);
		$theme = $course->themes()->create(['name' => 'Farmacia', 'program' => [
			['date_id' => $type->getKey(), 'name' => 'Lezione', 'hours' => '4.00'],
		]]);
		return CourseSession::create(['course_theme_id' => $theme->getKey(), 'name' => $name]);
	}

	public function test_participations_keep_independent_outcomes_and_are_accessible_from_both_parents() : void
	{
		$operator = $this->operator();
		$otherOperator = $this->operator();
		$edition = $this->edition();
		$otherEdition = $this->edition('Novembre');
		$participation = $edition->operatorCourseSessions()->create([
			'operator_id' => $operator->getKey(),
			'status' => 'iscritto',
		]);
		$otherParticipation = $edition->operatorCourseSessions()->create(['operator_id' => $otherOperator->getKey()]);
		$nextParticipation = $operator->operatorCourseSessions()->create(['course_session_id' => $otherEdition->getKey()]);

		$participation->update([
			'status' => 'completato', 'completed_at' => '2026-10-19 18:00:00',
			'result' => 'superato', 'note' => 'Verifica finale completata',
		]);
		$participation = $participation->fresh();
		$this->assertTrue(Str::isUuid($participation->getKey()));
		$this->assertInstanceOf(Carbon::class, $participation->completed_at);
		$this->assertSame('2026-10-19 18:00:00', $participation->completed_at->format('Y-m-d H:i:s'));
		$this->assertSame('superato', $participation->result);
		$this->assertSame('Verifica finale completata', $participation->note);
		$this->assertTrue($participation->operator->is($operator));
		$this->assertTrue($participation->courseSession->is($edition));
		$this->assertTrue($participation->courseSession->course->is($edition->course));
		$this->assertEqualsCanonicalizing([$participation->getKey(), $otherParticipation->getKey()], $edition->operatorCourseSessions->modelKeys());
		$this->assertEqualsCanonicalizing([$participation->getKey(), $nextParticipation->getKey()], $operator->operatorCourseSessions->modelKeys());
		$this->assertNull($otherParticipation->fresh()->completed_at);
		$this->assertNull($nextParticipation->fresh()->result);
	}

	public function test_the_database_rejects_duplicate_operator_edition_pairs() : void
	{
		$operator = $this->operator();
		$edition = $this->edition();
		$edition->operatorCourseSessions()->create(['operator_id' => $operator->getKey()]);

		$this->expectException(QueryException::class);
		DB::table(config('courses.models.operatorCourseSession.table'))->insert([
			'id' => (string) Str::uuid(), 'operator_id' => $operator->getKey(), 'course_session_id' => $edition->getKey(),
		]);
	}

	public function test_soft_deleted_participations_are_restored_without_losing_their_history() : void
	{
		$operator = $this->operator();
		$participation = $this->edition()->operatorCourseSessions()->create([
			'operator_id' => $operator->getKey(), 'result' => 'superato',
		]);
		$participation->delete();
		$this->assertCount(0, $operator->operatorCourseSessions);
		$this->assertSoftDeleted($participation);
		try {
			OperatorCourseSession::create($participation->only(['operator_id', 'course_session_id']));
			$this->fail('A deleted participation must be restored instead of duplicated.');
		} catch (QueryException $exception) {
			$this->assertDatabaseCount(config('courses.models.operatorCourseSession.table'), 1);
		}
		$participation->restore();
		$this->assertSame('superato', $participation->fresh()->result);
		$this->assertCount(1, $operator->fresh()->operatorCourseSessions);
	}

	public function test_history_and_outcomes_remain_available_after_parents_are_soft_deleted() : void
	{
		$operator = $this->operator();
		$edition = $this->edition();
		$participation = $edition->operatorCourseSessions()->create(['operator_id' => $operator->getKey()]);
		$operator->delete();
		$edition->delete();

		$participation = $participation->fresh();
		$this->assertTrue($participation->operator->trashed());
		$this->assertTrue($participation->courseSession->trashed());
		$participation->update(['result' => 'superato', 'completed_at' => '2026-10-19 18:00:00']);
		$this->assertSame('superato', $participation->fresh()->result);
	}

	public function test_new_participations_require_existing_active_parents() : void
	{
		$operator = $this->operator();
		$deletedOperator = $this->operator();
		$deletedOperator->delete();
		$edition = $this->edition();
		$deletedEdition = $this->edition('Eliminata');
		$deletedEdition->delete();

		foreach([
			[null, $edition->getKey(), 'operator_id'],
			['invalid', $edition->getKey(), 'operator_id'],
			[(string) Str::uuid(), $edition->getKey(), 'operator_id'],
			[$deletedOperator->getKey(), $edition->getKey(), 'operator_id'],
			[$operator->getKey(), null, 'course_session_id'],
			[$operator->getKey(), (string) Str::uuid(), 'course_session_id'],
			[$operator->getKey(), $deletedEdition->getKey(), 'course_session_id'],
		] as [$operatorId, $editionId, $error])
			try {
				OperatorCourseSession::create(['operator_id' => $operatorId, 'course_session_id' => $editionId]);
				$this->fail('Expected invalid parent to be rejected.');
			} catch (ValidationException $exception) {
				$this->assertArrayHasKey($error, $exception->errors());
			}
		$this->assertDatabaseCount(config('courses.models.operatorCourseSession.table'), 0);
	}

	public function test_changing_a_parent_cannot_assign_a_deleted_operator() : void
	{
		$participation = $this->edition()->operatorCourseSessions()->create(['operator_id' => $this->operator()->getKey()]);
		$operator = $this->operator();
		$operator->delete();

		$this->expectException(ValidationException::class);
		$participation->update(['operator_id' => $operator->getKey()]);
	}

	public function test_foreign_keys_protect_participations_even_when_model_validation_is_bypassed() : void
	{
		$operator = $this->operator();
		$edition = $this->edition();
		foreach([
			[(string) Str::uuid(), $edition->getKey()],
			[$operator->getKey(), (string) Str::uuid()],
		] as [$operatorId, $editionId])
			try {
				DB::table(config('courses.models.operatorCourseSession.table'))->insert([
					'id' => (string) Str::uuid(), 'operator_id' => $operatorId, 'course_session_id' => $editionId,
				]);
				$this->fail('Expected foreign key constraint to reject missing parent.');
			} catch (QueryException $exception) {
				$this->assertDatabaseCount(config('courses.models.operatorCourseSession.table'), 0);
			}
	}

	public function test_migration_can_be_rolled_back_and_uses_the_configured_table() : void
	{
		$migration = require __DIR__ . '/../../database/migrations/2026_10_19_000024_create_courses__operator_course_sessions_table.php';
		$clientMigration = require __DIR__ . '/../../database/migrations/2026_10_20_000025_add_client_id_to_courses__operator_course_sessions_table.php';
		$clientMigration->down();
		$migration->down();
		$this->assertFalse(Schema::hasTable('courses__operator_course_sessions'));
		config(['courses.models.operatorCourseSession.table' => 'custom_participations']);
		$migration->up();
		$clientMigration->up();
		$participation = $this->edition()->operatorCourseSessions()->create(['operator_id' => $this->operator()->getKey()]);
		$this->assertDatabaseHas('custom_participations', ['id' => $participation->getKey()]);
		$this->assertNull($participation->fresh()->client_id);
		$clientMigration->down();
		$migration->down();
		$this->assertFalse(Schema::hasTable('custom_participations'));
		$this->assertTrue(Schema::hasTable(config('operators.models.operator.table')));
		$this->assertTrue(Schema::hasTable(config('courses.models.courseSession.table')));
	}

	public function test_client_migration_preserves_existing_participations_and_keeps_the_reference_optional() : void
	{
		$migration = require __DIR__ . '/../../database/migrations/2026_10_20_000025_add_client_id_to_courses__operator_course_sessions_table.php';
		$migration->down();
		$participation = $this->edition()->operatorCourseSessions()->create([
			'operator_id' => $this->operator()->getKey(), 'result' => 'superato',
		]);
		$migration->up();
		$this->assertNull($participation->fresh()->client_id);
		$this->assertSame('superato', $participation->fresh()->result);

		// A client may be assigned later, even while the clients table is being reimported.
		$clientId = (string) Str::uuid();
		$participation->update(['client_id' => $clientId]);
		$this->assertSame($clientId, $participation->fresh()->client_id);
		$participation->update(['client_id' => null]);
		$this->assertNull($participation->fresh()->client_id);
		$this->expectException(ValidationException::class);
		$participation->update(['client_id' => 'invalid']);
	}
}

class CoursesTestOperator extends Operator
{
	use OperatorUsesCoursesTrait;

	protected $with = [];
}
