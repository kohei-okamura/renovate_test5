<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Closure;
use Domain\Common\Carbon;
use Illuminate\Support\LazyCollection;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftFinderMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\BulkCreateAttendanceInteractor;

/**
 * {@link \UseCase\Shift\BulkCreateAttendanceInteractor} のテスト.
 */
final class BulkCreateAttendanceInteractorTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use ShiftFinderMixin;
    use AttendanceRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    /**
     * @var \UseCase\Shift\BulkCreateAttendanceInteractor
     */
    private $interactor;

    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BulkCreateAttendanceInteractorTest $self): void {
            $self->shiftFinder
                ->allows('cursor')
                ->andReturn(LazyCollection::make([$self->examples->shifts[0]]))
                ->byDefault();

            $self->interactor = app(BulkCreateAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use cursor in the Finder', function (): void {
            $this->shiftFinder
                ->expects('cursor')
                ->with(
                    [
                        'organizationId' => $this->examples->organizations[0]->id,
                        'isConfirmed' => true,
                        'endDate' => Carbon::yesterday(),
                    ],
                    [
                        'sortBy' => 'id',
                    ]
                )
                ->andReturn(LazyCollection::empty())
                ->byDefault();

            $this->interactor->handle(Carbon::yesterday(), $this->examples->organizations[0]->id);
        });
        $this->should('store the Attendance after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run`に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->attendanceRepository
                        ->expects('store')
                        ->andReturn($this->examples->attendances[0]);
                    return $callback();
                });

            $this->interactor->handle(Carbon::yesterday(), $this->examples->organizations[0]->id);
        });
        $this->should('store Attendance 2 times when get 2 Shift entities', function (): void {
            $this->shiftFinder
                ->expects('cursor')
                ->with(
                    [
                        'organizationId' => $this->examples->organizations[0]->id,
                        'isConfirmed' => true,
                        'endDate' => Carbon::yesterday()->subDay(),
                    ],
                    [
                        'sortBy' => 'id',
                    ]
                )->andReturn(LazyCollection::make([
                    $this->examples->shifts[0],
                    $this->examples->shifts[1],
                ]))->byDefault();
            $this->attendanceRepository
                ->expects('store')
                ->andReturn($this->examples->attendances[0])
                ->times(2);

            $count = $this->interactor->handle(Carbon::yesterday()->subDay(), $this->examples->organizations[0]->id);
            $this->assertSame(
                2,
                $count
            );
        });
    }
}
