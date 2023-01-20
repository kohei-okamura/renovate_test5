<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Shift\Assignee;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NoConflictRule} のテスト.
 */
final class NoConflictRuleTest extends Test
{
    use FindShiftUseCaseMixin;
    use LookupShiftUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[0]->id)
                ->andReturn(Seq::from($self->examples->shifts[0]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[2]->id)
                ->andReturn(Seq::from($self->examples->shifts[2]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[7]->id)
                ->andReturn(Seq::from($self->examples->shifts[7]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[8]->id)
                ->andReturn(Seq::from($self->examples->shifts[8]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNoConflict(): void
    {
        $this->should('pass when the shift with given id does not exist', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
            $ids = [self::NOT_EXISTING_ID];

            $this->assertTrue($this->noConflictValidator($ids)->passes());
        });
        $this->should('fail when ids contain a conflict of a confirmed shift', function (): void {
            $this->findShiftUseCase
                ->allows('handle')
                ->andReturn(
                    FinderResult::from(
                        [$this->examples->shifts[3]],
                        Pagination::create(['all' => true])
                    )
                );
            $ids = [$this->examples->shifts[8]->id];

            $this->assertTrue($this->noConflictValidator($ids)->fails());
        });
        $this->should('fail when ids contain a conflict of ourselves', function (): void {
            $this->findShiftUseCase
                ->allows('handle')
                ->andReturn(
                    FinderResult::from(
                        [$this->examples->shifts[2], $this->examples->shifts[7]],
                        Pagination::create(['all' => true])
                    )
                );
            $ids = [$this->examples->shifts[2]->id, $this->examples->shifts[7]->id];

            $this->assertTrue($this->noConflictValidator($ids)->fails());
            $this->assertSame(
                ['ids.0' => ['勤務シフトが重複しています。'], 'ids.1' => ['勤務シフトが重複しています。']],
                $this->noConflictValidator($ids)->errors()->messages()
            );
        });
        $this->should('fail when a 2nd assignee of a shift with 2 assignees is in a conflict of a shift', function (): void {
            $this->findShiftUseCase
                ->allows('handle')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->shifts[0]->copy([
                                'id' => 50,
                                'assignees' => [
                                    Assignee::create([
                                        'staffId' => $this->examples->shifts[0]->assignees[1]->staffId,
                                        'isUndecided' => false,
                                        'isTraining' => false,
                                    ]),
                                ],
                                'schedule' => Schedule::create([
                                    'start' => $this->examples->shifts[0]->schedule->start,
                                    'end' => $this->examples->shifts[0]->schedule->end,
                                    'date' => $this->examples->shifts[0]->schedule->date,
                                ]),
                                'isConfirmed' => true,
                            ]),
                        ],
                        Pagination::create(['all' => true])
                    )
                )->byDefault();
            $ids = [$this->examples->shifts[0]->id];

            $this->assertTrue($this->noConflictValidator($ids)->fails());
            $this->assertSame(
                ['ids.0' => ['勤務シフトが重複しています。']],
                $this->noConflictValidator($ids)->errors()->messages()
            );
        });
    }

    /**
     * 勤務シフト確定時の「勤務シフトID」がダブルブッキングにならないことを検証するバリデータを生成する.
     *
     * @param int[] $ids
     * @return \App\Validations\CustomValidator
     */
    private function noConflictValidator(array $ids): CustomValidator
    {
        return $this->buildCustomValidator(
            ['ids' => $ids],
            ['ids.*' => 'no_conflict:ids,' . Permission::updateShifts()],
        );
    }
}
