<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\Task;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\RuntimeException;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Support functions for {@link \Domain\ServiceCodeDictionary\DwsServiceCodeCategory}.
 *
 * @mixin \Domain\ServiceCodeDictionary\DwsServiceCodeCategory
 */
trait DwsServiceCodeCategorySupport
{
    /**
     * 障害福祉サービス：重度訪問介護：特定事業所加算区分に対応するインスタンスを返す.
     *
     * @param \Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromVisitingCareForPwsdSpecifiedOfficeAddition(
        VisitingCareForPwsdSpecifiedOfficeAddition $x
    ): Option {
        if ($x === VisitingCareForPwsdSpecifiedOfficeAddition::none()) {
            return Option::none();
        }
        $map = [
            VisitingCareForPwsdSpecifiedOfficeAddition::addition1()->value() => static::specifiedOfficeAddition1(),
            VisitingCareForPwsdSpecifiedOfficeAddition::addition2()->value() => static::specifiedOfficeAddition2(),
            VisitingCareForPwsdSpecifiedOfficeAddition::addition3()->value() => static::specifiedOfficeAddition3(),
        ];
        return Option::fromArray($map, $x->value())->orElse(function () use ($x): void {
            // @codeCoverageIgnoreStart
            // 追加された場合に漏れをチェックする
            throw new InvalidArgumentException(
                "VisitingCareForPwsdSpecifiedOfficeAddition({$x->value()}) not supported."
            );
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * 障害福祉サービス：居宅訪問介護：特定事業所加算区分に対応するインスタンスを返す.
     *
     * @param \Domain\Office\HomeHelpServiceSpecifiedOfficeAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromHomeHelpServiceSpecifiedOfficeAddition(
        HomeHelpServiceSpecifiedOfficeAddition $x
    ): Option {
        if ($x === HomeHelpServiceSpecifiedOfficeAddition::none()) {
            return Option::none();
        }
        $map = [
            HomeHelpServiceSpecifiedOfficeAddition::addition1()->value() => static::specifiedOfficeAddition1(),
            HomeHelpServiceSpecifiedOfficeAddition::addition2()->value() => static::specifiedOfficeAddition2(),
            HomeHelpServiceSpecifiedOfficeAddition::addition3()->value() => static::specifiedOfficeAddition3(),
            HomeHelpServiceSpecifiedOfficeAddition::addition4()->value() => static::specifiedOfficeAddition4(),
        ];
        return Option::fromArray($map, $x->value())->orElse(function () use ($x): void {
            // @codeCoverageIgnoreStart
            // 追加された場合に漏れをチェックする
            throw new InvalidArgumentException(
                "HomeHelpServiceSpecifiedOfficeAddition({$x->value()}) not supported."
            );
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * 障害福祉サービス：福祉・介護職員処遇改善加算区分に対応するインスタンスを返す.
     *
     * @param \Domain\Office\DwsTreatmentImprovementAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromTreatmentImprovementAddition(DwsTreatmentImprovementAddition $x): Option
    {
        if ($x === DwsTreatmentImprovementAddition::none()) {
            return Option::none();
        }
        $map = [
            DwsTreatmentImprovementAddition::addition1()->value() => static::treatmentImprovementAddition1(),
            DwsTreatmentImprovementAddition::addition2()->value() => static::treatmentImprovementAddition2(),
            DwsTreatmentImprovementAddition::addition3()->value() => static::treatmentImprovementAddition3(),
            DwsTreatmentImprovementAddition::addition4()->value() => static::treatmentImprovementAddition4(),
            DwsTreatmentImprovementAddition::addition5()->value() => static::treatmentImprovementAddition5(),
        ];
        return Option::fromArray($map, $x->value())->orElse(function () use ($x): void {
            // @codeCoverageIgnoreStart
            // 追加された場合に漏れをチェックする
            throw new InvalidArgumentException(
                "DwsTreatmentImprovementAddition({$x->value()}) not supported."
            );
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * 障害福祉サービス：福祉・介護職員等特定処遇改善加算区分に対応するインスタンスを返す.
     *
     * @param \Domain\Office\DwsSpecifiedTreatmentImprovementAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromSpecifiedTreatmentImprovementAddition(
        DwsSpecifiedTreatmentImprovementAddition $x
    ): Option {
        if ($x === DwsSpecifiedTreatmentImprovementAddition::none()) {
            return Option::none();
        }
        $map = [
            DwsSpecifiedTreatmentImprovementAddition::addition1()->value() => static::specifiedTreatmentImprovementAddition1(),
            DwsSpecifiedTreatmentImprovementAddition::addition2()->value() => static::specifiedTreatmentImprovementAddition2(),
        ];
        return Option::fromArray($map, $x->value())->orElse(function () use ($x): void {
            // @codeCoverageIgnoreStart
            // 追加された場合に漏れをチェックする
            throw new InvalidArgumentException(
                "DwsSpecifiedTreatmentImprovementAddition({$x->value()}) not supported."
            );
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * 障害福祉サービス：計画：サービス区分に対応するインスタンスを返す（居宅のみ）
     *
     * @param \Domain\Project\DwsProjectServiceCategory $x
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory|static
     */
    public static function fromDwsProjectServiceCategory(DwsProjectServiceCategory $x): self
    {
        return match ($x) {
            DwsProjectServiceCategory::physicalCare() => static::physicalCare(),
            DwsProjectServiceCategory::housework() => static::housework(),
            DwsProjectServiceCategory::accompanyWithPhysicalCare() => static::accompanyWithPhysicalCare(),
            DwsProjectServiceCategory::accompany() => static::accompany(),
            default => throw new RuntimeException('Unsupported DwsProjectServiceCategory: ' . $x->key())
        };
    }

    /**
     * 障害福祉サービス：受給者証 に対応するインスタンスを返す（重訪のみ）.
     *
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory|static
     */
    public static function fromDwsCertification(DwsCertification $certification, Carbon $providedIn): self
    {
        return Seq::from(...$certification->grants)
            ->filter(function (DwsCertificationGrant $x) use ($providedIn): bool {
                return $x->dwsCertificationServiceType->isVisitingCareForPwsd()
                    && $x->activatedOn->startOfMonth() <= $providedIn
                    && $x->deactivatedOn >= $providedIn;
            })
            ->headOption()
            ->map(fn (DwsCertificationGrant $x): DwsServiceCodeCategory => match ($x->dwsCertificationServiceType) {
                DwsCertificationServiceType::visitingCareForPwsd1() => self::visitingCareForPwsd1(),
                DwsCertificationServiceType::visitingCareForPwsd2() => self::visitingCareForPwsd2(),
                DwsCertificationServiceType::visitingCareForPwsd3() => self::visitingCareForPwsd3(),
            })
            ->getOrElse(function (): void {
                throw new RuntimeException('Failed to determine DwsServiceCodeCategory');
            });
    }

    /**
     * （居宅介護の）勤務区分に対応するインスタンスを返す.
     *
     * @param \Domain\Shift\Task $task
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory|static
     */
    public static function fromTaskOfHomeHelpService(Task $task): self
    {
        return match ($task) {
            Task::dwsPhysicalCare() => DwsServiceCodeCategory::physicalCare(),
            Task::dwsHousework() => DwsServiceCodeCategory::housework(),
            Task::dwsAccompanyWithPhysicalCare() => DwsServiceCodeCategory::accompanyWithPhysicalCare(),
            Task::dwsAccompany() => DwsServiceCodeCategory::accompany(),
            default => throw new RuntimeException('Unsupported Task: ' . $task->key()),
        };
    }

    /**
     * 1日の区切り位置を取得する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $type
     * @param bool $isFirst
     * @return \Carbon\CarbonImmutable&\Domain\Common\Carbon
     */
    public function getDayBoundary(Carbon $start, DwsHomeHelpServiceProviderType $type, bool $isFirst): Carbon
    {
        // Carbon の `endOfDay` は 23:59:59 を返すため「翌日」の `startOfDay` を取得する.
        $endOfDay = $start->addDay()->startOfDay();
        $duration = $start->diffInMinutes($endOfDay);
        $minDurationMinutesForStart = $this->getMinDurationMinutes($type, $isFirst);
        $minDurationMinutes = $this->getMinDurationMinutes($type, isFirst: false);

        // 最初の最小単位で日を跨いでいる場合は最小単位の終わりが1日の区切り位置となる.
        if ($duration < $minDurationMinutesForStart) {
            return $start->addMinutes($minDurationMinutesForStart);
        }

        // 1日目の時間数が最小単位で割り切れるようにする.
        $fraction = ($duration - $minDurationMinutesForStart) % $minDurationMinutes;
        return $fraction === 0
            ? $endOfDay
            : $endOfDay->addMinutes($minDurationMinutes - $fraction);
    }

    /**
     * サービスコード区分ごとに異なる最小単位を返す.
     *
     * - 下記区分は30分
     *     - 身体介護
     *     - 通院等介助（身体を伴う）
     *     - 通院等介助（身体を伴わない）
     * - 下記区分は15分（ただし一連のサービスにおける最初の最小単位のみ30分）
     *     - 家事援助
     * - 下記区分はサービスの最初かつ重度訪問介護研修修了者による場合60分
     *     - 身体介護
     *     - 通院等介助（身体を伴う）
     *
     * @param DwsHomeHelpServiceProviderType $type
     * @param bool $isFirst
     * @return int
     */
    public function getMinDurationMinutes(DwsHomeHelpServiceProviderType $type, bool $isFirst): int
    {
        return match ($this) {
            self::physicalCare(),
            self::accompanyWithPhysicalCare() => $isFirst && $type === DwsHomeHelpServiceProviderType::careWorkerForPwsd()
                ? 60
                : 30,
            self::accompany() => 30,
            self::housework() => $isFirst ? 30 : 15,
            default => throw new LogicException("cannot resolve min duration minutes for: {$this}")
        };
    }

    /**
     * サービスコード区分が居宅かどうかを判定する.
     *
     * @return bool
     */
    public function isHomeHelpService(): bool
    {
        return match ($this) {
            self::physicalCare(),
            self::housework(),
            self::accompanyWithPhysicalCare(),
            self::accompany(),
            self::accessibleTaxi() => true,
            default => false
        };
    }

    /**
     * サービスコード区分が身体を伴うかどうかを判定する.
     *
     * @return bool
     */
    public function isHomeHelpServiceWithPhysicalCare(): bool
    {
        return match ($this) {
            self::physicalCare(),
            self::accompanyWithPhysicalCare(),
            self::accessibleTaxi() => true,
            default => false
        };
    }
}
