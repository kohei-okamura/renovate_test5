<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Common\ServiceSegment;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Support functions for {@link \Domain\Shift\Task}.
 *
 * @mixin \Domain\Shift\Task
 */
trait TaskSupport
{
    /**
     * {@link \Domain\Shift\Task} に対応した {@link \Domain\Shift\Activity} の配列を返す.
     *
     * @return array|\Domain\Shift\Activity[]
     */
    public function toActivities(): array
    {
        switch ($this) {
            case self::dwsPhysicalCare():
                return [Activity::dwsPhysicalCare()];
            case self::dwsHousework():
                return [Activity::dwsHousework()];
            case self::dwsAccompanyWithPhysicalCare():
                return [Activity::dwsAccompanyWithPhysicalCare()];
            case self::dwsAccompany():
                return [Activity::dwsAccompany()];
            case self::dwsVisitingCareForPwsd():
                return [Activity::dwsVisitingCareForPwsd()];
            case self::ltcsPhysicalCare():
                return [Activity::ltcsPhysicalCare()];
            case self::ltcsHousework():
                return [Activity::ltcsHousework()];
            case self::ltcsPhysicalCareAndHousework():
                return [Activity::ltcsPhysicalCare(), Activity::ltcsHousework()];
            case self::comprehensive():
                return [Activity::comprehensive()];
            case self::commAccompanyWithPhysicalCare():
                return [Activity::commAccompanyWithPhysicalCare()];
            case self::commAccompany():
                return [Activity::commAccompany()];
            case self::ownExpense():
                return [Activity::ownExpense()];
            case self::fieldwork():
                return [Activity::fieldwork()];
            case self::assessment():
                return [Activity::assessment()];
            case self::visit():
                return [Activity::visit()];
            case self::officeWork():
                return [Activity::officeWork()];
            case self::sales():
                return [Activity::sales()];
            case self::meeting():
                return [Activity::meeting()];
            case self::other():
                return [Activity::other()];
            default:
                throw new LogicException('Unexpected task value'); // @codeCoverageIgnore
        }
    }

    /**
     * {@link \Domain\Shift\Task} に対応した {@link \Domain\Shift\Activity} の一覧を返す.
     *
     * @return \Domain\Shift\Activity[]|\ScalikePHP\Seq
     */
    public function toActivitiesSeq(): Seq
    {
        $xs = $this->toActivities();
        return Seq::fromArray($xs);
    }

    /**
     * Task に対応した ServiceSegment の Option を返す.
     *
     * @return \Domain\Common\ServiceSegment[]|\ScalikePHP\Option
     */
    public function toServiceSegment(): Option
    {
        switch ($this) {
            case self::dwsPhysicalCare():
            case self::dwsHousework():
            case self::dwsAccompanyWithPhysicalCare():
            case self::dwsAccompany():
            case self::dwsVisitingCareForPwsd():
                return Option::from(ServiceSegment::disabilitiesWelfare());
            case self::ltcsPhysicalCare():
            case self::ltcsHousework():
            case self::ltcsPhysicalCareAndHousework():
                return Option::from(ServiceSegment::longTermCare());
            case self::comprehensive():
                return Option::from(ServiceSegment::comprehensive());
            case self::commAccompanyWithPhysicalCare():
            case self::commAccompany():
                return Option::from(ServiceSegment::communityLifeSupport());
            case self::ownExpense():
                return Option::from(ServiceSegment::ownExpense());
            case self::fieldwork():
            case self::assessment():
            case self::visit():
            case self::officeWork():
            case self::sales():
            case self::meeting():
            case self::other():
                return Option::none();
            default:
                throw new LogicException('Unexpected task value'); // @codeCoverageIgnore
        }
    }
}
