<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Attributes\JsonIgnore;
use Domain\Common\Carbon;
use Domain\Polite;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求：サービス詳細.
 */
final class LtcsBillingServiceDetail extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingServiceDetail} constructor.
     *
     * @param int $userId 利用者 ID
     * @param \Domain\Billing\LtcsBillingServiceDetailDisposition $disposition 区分
     * @param \Domain\Common\Carbon $providedOn サービス提供年月日
     * @param \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
     * @param \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $serviceCodeCategory サービスコード区分
     * @param \Domain\ProvisionReport\LtcsBuildingSubtraction $buildingSubtraction 同一建物減算区分
     * @param \Domain\ServiceCodeDictionary\LtcsNoteRequirement $noteRequirement 摘要欄記載要件
     * @param bool $isAddition 加算フラグ
     * @param bool $isLimited 支給限度額対象フラグ
     * @param int $durationMinutes 所要時間
     * @param int $unitScore 単位数
     * @param int $count 回数
     * @param int $wholeScore 総サービス単位数
     * @param int $maxBenefitQuotaExcessScore 種類支給限度基準を超える単位数
     * @param int $maxBenefitExcessScore 区分支給限度基準を超える単位数
     * @param int $totalScore サービス単位数
     */
    public function __construct(
        #[JsonIgnore] public readonly int $userId,
        #[JsonIgnore] public readonly LtcsBillingServiceDetailDisposition $disposition,
        #[JsonIgnore] public readonly Carbon $providedOn,
        #[JsonIgnore] public readonly ServiceCode $serviceCode,
        #[JsonIgnore] public readonly LtcsServiceCodeCategory $serviceCodeCategory,
        #[JsonIgnore] public readonly LtcsBuildingSubtraction $buildingSubtraction,
        #[JsonIgnore] public readonly LtcsNoteRequirement $noteRequirement,
        #[JsonIgnore] public readonly bool $isAddition,
        #[JsonIgnore] public readonly bool $isLimited,
        #[JsonIgnore] public readonly int $durationMinutes,
        #[JsonIgnore] public readonly int $unitScore,
        #[JsonIgnore] public readonly int $count,
        #[JsonIgnore] public readonly int $wholeScore,
        #[JsonIgnore] public readonly int $maxBenefitQuotaExcessScore,
        #[JsonIgnore] public readonly int $maxBenefitExcessScore,
        #[JsonIgnore] public readonly int $totalScore
    ) {
    }

    /**
     * 限度額管理対象・限度額管理対象外それぞれの単位数を集計する.
     *
     * @param \ScalikePHP\Seq&self[] $details
     * @param int $excessScore
     * @return array&int[]
     */
    public static function aggregateScore(Seq $details, int $excessScore): array
    {
        [$managed, $unmanaged] = $details->partition(fn (LtcsBillingServiceDetail $x): bool => $x->isLimited);
        return [
            $managed->map(fn (LtcsBillingServiceDetail $x): int => $x->totalScore)->sum() - $excessScore,
            $unmanaged->map(fn (LtcsBillingServiceDetail $x): int => $x->totalScore)->sum(),
        ];
    }
}
