<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Billing\DwsBillingStatementAggregate as Aggregate;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Contract\Contract;
use Domain\DwsCertification\DwsCertification;
use Domain\Polite;
use Domain\User\UserDwsSubsidy;
use Lib\Math;
use ScalikePHP\Option;

/**
 * 障害福祉サービス：明細書：集計.
 */
final class DwsBillingStatementAggregate extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingStatementAggregate} constructor.
     *
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode サービス種類コード
     * @param \Domain\Common\Carbon $startedOn サービス開始年月日
     * @param null|\Domain\Common\Carbon $terminatedOn サービス終了年月日
     * @param int $serviceDays サービス利用日数
     * @param int $subtotalScore 給付単位数
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     * @param int $subtotalFee 総費用額
     * @param int $unmanagedCopay 1割相当額
     * @param int $managedCopay 利用者負担額
     * @param int $cappedCopay 上限月額調整
     * @param null|int $adjustedCopay 調整後利用者負担額
     * @param null|int $coordinatedCopay 上限額管理後利用者負担額
     * @param int $subtotalCopay 決定利用者負担額
     * @param int $subtotalBenefit 請求額：給付費
     * @param null|int $subtotalSubsidy 自治体助成分請求額
     */
    public function __construct(
        public readonly DwsServiceDivisionCode $serviceDivisionCode,
        public readonly Carbon $startedOn,
        public readonly ?Carbon $terminatedOn,
        public readonly int $serviceDays,
        public readonly int $subtotalScore,
        public readonly Decimal $unitCost,
        public readonly int $subtotalFee,
        public readonly int $unmanagedCopay,
        public readonly int $managedCopay,
        public readonly int $cappedCopay,
        public readonly ?int $adjustedCopay,
        public readonly ?int $coordinatedCopay,
        public readonly int $subtotalCopay,
        public readonly int $subtotalBenefit,
        public readonly ?int $subtotalSubsidy
    ) {
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Contract\Contract $contract
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\User\UserDwsSubsidy[]&\ScalikePHP\Option $userSubsidyOption
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @param int $aggregatesCount 集計の総数
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     * @param int $serviceDays サービス利用日数
     * @param int $subtotalScore 給付単位数
     * @param int $consumedAdjustedCopay 他の集計で充当済みの調整後利用者負担額
     * @param int[]&\ScalikePHP\Option $managedCopayOption 利用者負担額
     * @param int[]&\ScalikePHP\Option $coordinatedCopayOption 上限額管理後利用者負担額
     * @param int[]&\ScalikePHP\Option $subtotalSubsidyOption 自治体助成分請求額
     * @return static
     */
    public static function from(
        Contract $contract,
        DwsCertification $certification,
        Option $userSubsidyOption,
        DwsServiceDivisionCode $serviceDivisionCode,
        int $aggregatesCount,
        Decimal $unitCost,
        int $serviceDays,
        int $subtotalScore,
        int $consumedAdjustedCopay,
        Option $managedCopayOption,
        Option $coordinatedCopayOption,
        Option $subtotalSubsidyOption
    ): self {
        $serviceDivisionCodeValue = $serviceDivisionCode->value();
        $startedOn = $contract->dwsPeriods[$serviceDivisionCodeValue]->start;
        $terminatedOn = $contract->dwsPeriods[$serviceDivisionCodeValue]->end;
        $subtotalFee = self::computeSubtotalFee($subtotalScore, $unitCost);
        $unmanagedCopay = self::computeUnmanagedCopay($subtotalFee, $certification);
        $managedCopay = $managedCopayOption->getOrElseValue($unmanagedCopay);
        $cappedCopay = self::computeCappedCopay($managedCopay, $certification);
        $adjustedCopay = self::computeAdjustedCopay(
            $aggregatesCount,
            $cappedCopay,
            $consumedAdjustedCopay,
            $certification
        );
        $coordinatedCopay = self::computeCoordinatedCopay($coordinatedCopayOption, $adjustedCopay, $cappedCopay);
        $subtotalCopay = self::computeSubtotalCopay($coordinatedCopay, $adjustedCopay, $cappedCopay);
        $subtotalSubsidy = $subtotalSubsidyOption
            ->orElse(fn (): Option => self::computeSubtotalSubsidy(
                $subtotalCopay,
                $subtotalFee,
                $userSubsidyOption
            ))
            ->orNull();
        return new Aggregate(
            serviceDivisionCode: $serviceDivisionCode,
            startedOn: $startedOn,
            terminatedOn: $terminatedOn,
            serviceDays: $serviceDays,
            subtotalScore: $subtotalScore,
            unitCost: $unitCost,
            subtotalFee: $subtotalFee,
            unmanagedCopay: $unmanagedCopay,
            managedCopay: $managedCopay,
            cappedCopay: $cappedCopay,
            adjustedCopay: $adjustedCopay,
            coordinatedCopay: $coordinatedCopay,
            subtotalCopay: $subtotalCopay,
            subtotalBenefit: self::computeSubtotalBenefit($subtotalFee, $subtotalCopay),
            subtotalSubsidy: $subtotalSubsidy
        );
    }

    /**
     * 《総費用額》を算出する.
     *
     * @param int $subtotalScore
     * @param \Domain\Common\Decimal $unitCost
     * @return int
     */
    private static function computeSubTotalFee(int $subtotalScore, Decimal $unitCost): int
    {
        return Math::floor($subtotalScore * $unitCost->toFloat());
    }

    /**
     * 《1割相当額》を算出する.
     *
     * 1割相当額, と呼ばれるが受給者証に記載されている利用者負担割合を用いて算出する.
     * 基本的にはそこに1割と記載されているが稀に例外がある.
     *
     * @param int $subtotalFee
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @return int
     */
    private static function computeUnmanagedCopay(int $subtotalFee, DwsCertification $certification): int
    {
        return Math::floor($subtotalFee * $certification->copayRate / 100);
    }

    /**
     * 《上限月額調整》を算出する.
     *
     * 《利用者負担額》と《利用者負担上限月額》の少ない方の値を用いる.
     *
     * @param int $managedCopay
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @return int
     */
    private static function computeCappedCopay(int $managedCopay, DwsCertification $certification): int
    {
        return min($managedCopay, $certification->copayLimit);
    }

    /**
     * 《調整後利用者負担額》を算出する.
     *
     * - 複数種類のサービスを提供した場合＝集計が2つ以上になる場合のみ算出する.
     * - 複数の集計の《上限月額調整》の合計が《利用者負担上限月額》超えている場合の調整を行う項目.
     * - 複数の集計の《調整後利用者負担額》の合計が《利用者負担上限月額》を超えないように調整する.
     *
     * @param int $aggregatesCount 集計の数
     * @param int $cappedCopay 上限月額調整
     * @param int $consumedAdjustedCopay 他の集計で算定済みの調整後利用者負担額
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @return null|int
     */
    private static function computeAdjustedCopay(
        int $aggregatesCount,
        int $cappedCopay,
        int $consumedAdjustedCopay,
        DwsCertification $certification
    ): ?int {
        return $aggregatesCount <= 1 ? null : min($cappedCopay, $certification->copayLimit - $consumedAdjustedCopay);
    }

    /**
     * 《上限額管理後利用者負担額》を算出する.
     *
     * - 上限管理結果「管理結果額」が設定されている場合のみ算出する.
     * - 以下の金額の内、最も少ない金額を《上限額管理後利用者負担額》とする.
     *     - 上限管理結果「管理結果額」の値
     *     - 《調整後利用者負担額》がある場合はその値、ない場合は《上限月額調整》の値
     *
     * @param int[]&\ScalikePHP\Option $coordinatedCopay
     * @param null|int $adjustedCopay
     * @param int $cappedCopay
     * @return null|int
     */
    private static function computeCoordinatedCopay(
        Option $coordinatedCopay,
        ?int $adjustedCopay,
        int $cappedCopay
    ): ?int {
        return $coordinatedCopay
            ->map(fn (int $x): int => min($x, $adjustedCopay ?? $cappedCopay))
            ->orNull();
    }

    /**
     * 《決定利用者負担額》を算出する.
     *
     * 自治体助成の有無に関わらず常に次の順序で判定して算出する.
     *
     * - 《上限額管理後利用者負担額》がある場合はその値
     * - 《調整後利用者負担額》がある場合はその値
     * - 上記のいずれもない場合は《上限月額調整》の値
     *
     * @param null|int $coordinatedCopay
     * @param null|int $adjustedCopay
     * @param int $cappedCopay
     * @return int
     */
    private static function computeSubtotalCopay(?int $coordinatedCopay, ?int $adjustedCopay, int $cappedCopay): int
    {
        return $coordinatedCopay ?? $adjustedCopay ?? $cappedCopay;
    }

    /**
     * 《請求額：給付費》を算出する.
     *
     * 自治体助成の有無に関わらず常に《総費用額》から《決定利用者負担額》を引いた金額となる.
     *
     * @param int $subtotalFee
     * @param int $subtotalCopay
     * @return int
     */
    private static function computeSubtotalBenefit(int $subtotalFee, int $subtotalCopay): int
    {
        return $subtotalFee - $subtotalCopay;
    }

    /**
     * 《自治体助成分請求額》を算出する.
     *
     * @param int $subtotalCopay 決定利用者負担額
     * @param int $subtotalFee 総費用額
     * @param \Domain\User\UserDwsSubsidy[]&\ScalikePHP\Option $userSubsidyOption
     * @return int[]&\ScalikePHP\Option
     */
    private static function computeSubtotalSubsidy(
        int $subtotalCopay,
        int $subtotalFee,
        Option $userSubsidyOption
    ): Option {
        return $userSubsidyOption
            ->map(fn (UserDwsSubsidy $subsidy): int => $subsidy->compute($subtotalCopay, $subtotalFee));
    }
}
