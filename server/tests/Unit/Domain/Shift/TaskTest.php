<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Common\ServiceSegment;
use Domain\Shift\Activity;
use Domain\Shift\Task;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Shift\Task} のテスト.
 */
final class TaskTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toActivitiesSeq(): void
    {
        $this->should('return seq of activity', function (Task $x, array $expect): void {
            $this->assertEquals(Seq::fromArray($expect), $x->toActivitiesSeq());
        }, ['examples' => [
            'with dwsPhysicalCare' => [Task::dwsPhysicalCare(), [Activity::dwsPhysicalCare()]],
            'with dwsHousework' => [Task::dwsHousework(), [Activity::dwsHousework()]],
            'with dwsAccompanyWithPhysicalCare' => [Task::dwsAccompanyWithPhysicalCare(), [Activity::dwsAccompanyWithPhysicalCare()]],
            'with dwsAccompany' => [Task::dwsAccompany(), [Activity::dwsAccompany()]],
            'with dwsVisitingCareForPwsd' => [Task::dwsVisitingCareForPwsd(), [Activity::dwsVisitingCareForPwsd()]],
            'with ltcsPhysicalCare' => [Task::ltcsPhysicalCare(), [Activity::ltcsPhysicalCare()]],
            'with ltcsHousework' => [Task::ltcsHousework(), [Activity::ltcsHousework()]],
            'with ltcsPhysicalCareAndHousework' => [
                Task::ltcsPhysicalCareAndHousework(), [Activity::ltcsPhysicalCare(), Activity::ltcsHousework()],
            ],
            'with commAccompanyWithPhysicalCare' => [Task::commAccompanyWithPhysicalCare(), [Activity::commAccompanyWithPhysicalCare()]],
            'with commAccompany' => [Task::commAccompany(), [Activity::commAccompany()]],
            'with comprehensive' => [Task::comprehensive(), [Activity::comprehensive()]],
            'with ownExpense' => [Task::ownExpense(), [Activity::ownExpense()]],
            'with fieldwork' => [Task::fieldwork(), [Activity::fieldwork()]],
            'with assessment' => [Task::assessment(), [Activity::assessment()]],
            'with visit' => [Task::visit(), [Activity::visit()]],
            'with officeWork' => [Task::officeWork(), [Activity::officeWork()]],
            'with sales' => [Task::sales(), [Activity::sales()]],
            'with meeting' => [Task::meeting(), [Activity::meeting()]],
            'with other' => [Task::other(), [Activity::other()]],
        ]]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_toServiceSegment(): void
    {
        $this->should('return option of ServiceSegment', function (Task $x, $expect): void {
            $this->assertEquals(Option::from($expect), $x->toServiceSegment());
        }, ['examples' => [
            'with dwsPhysicalCare' => [Task::dwsPhysicalCare(), ServiceSegment::disabilitiesWelfare()],
            'with dwsHousework' => [Task::dwsHousework(), ServiceSegment::disabilitiesWelfare()],
            'with dwsAccompanyWithPhysicalCare' => [Task::dwsAccompanyWithPhysicalCare(), ServiceSegment::disabilitiesWelfare()],
            'with dwsAccompany' => [Task::dwsAccompany(), ServiceSegment::disabilitiesWelfare()],
            'with dwsVisitingCareForPwsd' => [Task::dwsVisitingCareForPwsd(), ServiceSegment::disabilitiesWelfare()],
            'with ltcsPhysicalCare' => [Task::ltcsPhysicalCare(), ServiceSegment::longTermCare()],
            'with ltcsHousework' => [Task::ltcsHousework(), ServiceSegment::longTermCare()],
            'with ltcsPhysicalCareAndHousework' => [Task::ltcsPhysicalCareAndHousework(), ServiceSegment::longTermCare()],
            'with commAccompanyWithPhysicalCare' => [Task::commAccompanyWithPhysicalCare(), ServiceSegment::communityLifeSupport()],
            'with commAccompany' => [Task::commAccompany(), ServiceSegment::communityLifeSupport()],
            'with comprehensive' => [Task::comprehensive(), ServiceSegment::comprehensive()],
            'with ownExpense' => [Task::ownExpense(), ServiceSegment::ownExpense()],
            'with fieldwork' => [Task::fieldwork(), null],
            'with assessment' => [Task::assessment(), null],
            'with visit' => [Task::visit(), null],
            'with officeWork' => [Task::officeWork(), null],
            'with sales' => [Task::sales(), null],
            'with meeting' => [Task::meeting(), null],
            'with other' => [Task::other(), null],
        ]]);
    }
}
