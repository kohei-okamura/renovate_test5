<?php

/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shift;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Infrastructure\Shift\ShiftRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * ShiftRepositoryEloquentImpl のテスト.
 */
class ShiftRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private ShiftRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ShiftRepositoryEloquentImplTest $self): void {
            $self->repository = app(ShiftRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $actual = $this->repository->lookup($this->examples->shifts[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->shifts[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
        $this->should('return an entity when entity has multiple Assignee', function (): void {
            $shift = $this->examples->shifts[0]->copy([
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
            $this->repository->store($shift);
            $actual = $this->repository->lookup($this->examples->shifts[0]->id);
            $this->assertModelStrictEquals(
                $shift,
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
                        'duration' => 100,
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
            $entity = Shift::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('備考の内容', $this->examples->shifts[0]->note);
            $shift = $this->examples->shifts[0]->copy(['note' => '備考の内容']);
            $this->repository->store($shift);

            $actual = $this->repository->lookup($this->examples->shifts[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $shift,
                $actual->head()
            );
        });
        $this->should('update the entity that have option', function (): void {
            $this->assertNotEquals('備考の内容', $this->examples->shifts[1]->note);
            $shift = $this->examples->shifts[1]->copy(['note' => '備考の内容']);
            $this->repository->store($shift);

            $actual = $this->repository->lookup($this->examples->shifts[1]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $shift,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->shifts[0]->copy(['note' => '備考の内容']);
            $this->assertNotEquals('備考の内容', $this->examples->shifts[0]->note);

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
            $this->repository->removeById($this->examples->shifts[5]->id, $this->examples->shifts[6]->id); // リレーションのないID
            $shift0 = $this->repository->lookup($this->examples->shifts[5]->id);
            $this->assertCount(0, $shift0);
            $shift1 = $this->repository->lookup($this->examples->shifts[6]->id);
            $this->assertCount(0, $shift1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->shifts[5]->id);
            $shift0 = $this->repository->lookup($this->examples->shifts[5]->id);
            $this->assertCount(0, $shift0);
            $shift1 = $this->repository->lookup($this->examples->shifts[1]->id);
            $this->assertCount(1, $shift1);
            $this->assertModelStrictEquals($this->examples->shifts[1], $shift1->head());
        });
    }
}
