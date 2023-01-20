<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Lib\Exceptions\RuntimeException;
use ScalikePHP\Option;

/**
 * 介護保険サービス：サービスコード区分.
 *
 * @mixin \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory
 */
trait LtcsServiceCodeCategorySupport
{
    /**
     * 介護保険サービス：同一建物減算区分からインスタンスを生成する.
     *
     * @param \Domain\ProvisionReport\LtcsBuildingSubtraction $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromBuildingSubtraction(LtcsBuildingSubtraction $x): Option
    {
        return match ($x) {
            LtcsBuildingSubtraction::none() => Option::none(),
            LtcsBuildingSubtraction::subtraction1() => Option::some(self::bulkServiceSubtraction1()),
            LtcsBuildingSubtraction::subtraction2() => Option::some(self::bulkServiceSubtraction2()),
        };
    }

    /**
     * 介護保険サービス：介護職員処遇改善加算区分からインスタンスを生成する.
     *
     * @param \Domain\Office\LtcsOfficeLocationAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromOfficeLocationAddition(LtcsOfficeLocationAddition $x): Option
    {
        return match ($x) {
            LtcsOfficeLocationAddition::none() => Option::none(),
            LtcsOfficeLocationAddition::specifiedArea() => Option::some(self::specifiedAreaAddition()),
            LtcsOfficeLocationAddition::mountainousArea() => Option::some(self::smallOfficeAddition()),
        };
    }

    /**
     * 介護保険サービス：介護職員処遇改善加算区分からインスタンスを生成する.
     *
     * @param \Domain\Office\LtcsTreatmentImprovementAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromTreatmentImprovementAddition(LtcsTreatmentImprovementAddition $x): Option
    {
        return match ($x) {
            LtcsTreatmentImprovementAddition::none() => Option::none(),
            LtcsTreatmentImprovementAddition::addition1() => Option::some(self::treatmentImprovementAddition1()),
            LtcsTreatmentImprovementAddition::addition2() => Option::some(self::treatmentImprovementAddition2()),
            LtcsTreatmentImprovementAddition::addition3() => Option::some(self::treatmentImprovementAddition3()),
            LtcsTreatmentImprovementAddition::addition4() => Option::some(self::treatmentImprovementAddition4()),
            LtcsTreatmentImprovementAddition::addition5() => Option::some(self::treatmentImprovementAddition5()),
        };
    }

    /**
     * 介護保険サービス：介護職員等特定処遇改善加算区分からインスタンスを生成する.
     *
     * @param \Domain\Office\LtcsSpecifiedTreatmentImprovementAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromSpecifiedTreatmentImprovementAddition(
        LtcsSpecifiedTreatmentImprovementAddition $x
    ): Option {
        return match ($x) {
            LtcsSpecifiedTreatmentImprovementAddition::none() => Option::none(),
            LtcsSpecifiedTreatmentImprovementAddition::addition1() => Option::some(self::specifiedTreatmentImprovementAddition1()),
            LtcsSpecifiedTreatmentImprovementAddition::addition2() => Option::some(self::specifiedTreatmentImprovementAddition2()),
        };
    }

    /**
     * 介護保険サービス：ベースアップ等支援加算からインスタンスを生成する.
     *
     * @param \Domain\Office\LtcsBaseIncreaseSupportAddition $x
     * @return \ScalikePHP\Option&static[]
     */
    public static function fromBaseIncreaseSupportAddition(
        LtcsBaseIncreaseSupportAddition $x
    ): Option {
        return match ($x) {
            LtcsBaseIncreaseSupportAddition::none() => Option::none(),
            LtcsBaseIncreaseSupportAddition::addition1() => Option::some(self::baseIncreaseSupportAddition()),
        };
    }

    /**
     * 介護保険サービス：計画：サービス区分からインスタンスを生成する.
     *
     * @param \Domain\Project\LtcsProjectServiceCategory $x
     * @return static
     */
    public static function fromLtcsProjectServiceCategory(LtcsProjectServiceCategory $x): self
    {
        return match ($x) {
            LtcsProjectServiceCategory::physicalCare() => self::physicalCare(),
            LtcsProjectServiceCategory::physicalCareAndHousework() => self::physicalCareAndHousework(),
            LtcsProjectServiceCategory::housework() => self::housework(),
            default => throw new RuntimeException('Unsupported Enum: ' . $x->key()),
        };
    }

    /**
     * サービスコード区分が訪問介護サービス（加算を除く）を伴うかどうかを判定する.
     *
     * @return bool
     */
    public function isHomeVisitLongTermCare(): bool
    {
        return match ($this) {
            self::physicalCare(),
            self::physicalCareAndHousework(),
            self::housework() => true,
            default => false,
        };
    }

    /**
     * サービスコード区分が身体を伴うかどうかを判定する.
     *
     * @return bool
     */
    public function isHomeVisitLongTermCareWithPhysicalCare(): bool
    {
        return match ($this) {
            self::physicalCare(),
            self::physicalCareAndHousework() => true,
            default => false,
        };
    }
}
