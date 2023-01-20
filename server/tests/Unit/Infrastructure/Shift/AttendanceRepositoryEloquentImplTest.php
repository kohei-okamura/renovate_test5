<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shift;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Attendance;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Infrastructure\Shift\AttendanceRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * AttendanceRepositoryEloquentImpl のテスト.
 */
final class AttendanceRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private AttendanceRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (AttendanceRepositoryEloquentImplTest $self): void {
            $self->repository = app(AttendanceRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $actual = $this->repository->lookup($this->examples->attendances[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->attendances[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
        $this->should('return an entity when entity has multiple Assignee', function (): void {
            $result = $this->examples->attendances[0]->copy([
                'details' => [
                    Assignee::create([
                        'staffId' => $this->examples->staffs[9]->id,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                    Assignee::create([
                        'staffId' => $this->examples->staffs[10]->id,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                ],
            ]);
            $this->repository->store($result);
            $actual = $this->repository->lookup($this->examples->attendances[0]->id);
            $this->assertModelStrictEquals(
                $result,
                $actual->head()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store()
    {
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'organizationId' => $this->examples->organizations[0]->id,
                'contractId' => $this->examples->contracts[0]->id,
                'officeId' => $this->examples->offices[0]->id,
                'userId' => $this->examples->users[0]->id,
                'assignerId' => $this->examples->staffs[8]->id,
                'task' => Task::commAccompany(),
                'serviceCode' => ServiceCode::fromString('123456'),
                'headcount' => 1,
                'assignees' => [
                    Assignee::create([
                        'staffId' => $this->examples->staffs[10]->id,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                ],
                'schedule' => Schedule::create(
                    [
                        'start' => Carbon::create(2018, 3, 1, 0, 0)->format('Y-m-d 10:00:00'),
                        'end' => Carbon::create(2018, 3, 1, 9, 0)->format('Y-m-d 10:00:00'),
                        'date' => Carbon::create(2018, 3, 1)->startOfDay()->format('Y-m-d'),
                    ]
                ),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::commAccompany(),
                        'duration' => 100,
                    ]),
                    Duration::create([
                        'activity' => Activity::commAccompany(),
                        'duration' => 200,
                    ]),
                ],
                'options' => [
                    ServiceOption::firstTime(),
                ],
                'note' => 'ここに備考を書く',
                'isConfirmed' => false,
                'isCanceled' => false,
                'reason' => '',
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = Attendance::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('備考の内容', $this->examples->attendances[0]->note);
            $result = $this->examples->attendances[0]->copy(['note' => '備考の内容']);
            $this->repository->store($result);

            $actual = $this->repository->lookup($this->examples->attendances[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $result,
                $actual->head()
            );
        });
        $this->should('update the entity that have option', function (): void {
            $this->assertNotEquals('備考の内容', $this->examples->attendances[1]->note);
            $result = $this->examples->attendances[1]->copy(['note' => '備考の内容']);
            $this->repository->store($result);

            $actual = $this->repository->lookup($this->examples->attendances[1]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $result,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->attendances[0]->copy(['note' => '備考の内容']);
            $this->assertNotEquals('備考の内容', $this->examples->attendances[0]->note);

            $this->assertModelStrictEquals(
                $entity,
                $this->repository->store($entity)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById(
                $this->examples->attendances[0]->id,
                $this->examples->attendances[1]->id
            ); // リレーションのないID
            $result0 = $this->repository->lookup($this->examples->attendances[0]->id);
            $this->assertCount(0, $result0);
            $result1 = $this->repository->lookup($this->examples->attendances[1]->id);
            $this->assertCount(0, $result1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->attendances[0]->id);
            $result0 = $this->repository->lookup($this->examples->attendances[0]->id);
            $this->assertCount(0, $result0);
            $result1 = $this->repository->lookup($this->examples->attendances[1]->id);
            $this->assertCount(1, $result1);
            $this->assertModelStrictEquals($this->examples->attendances[1], $result1->head());
        });
    }
}
