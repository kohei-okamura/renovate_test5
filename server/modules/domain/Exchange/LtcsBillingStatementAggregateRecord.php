<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Common\Carbon;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：伝送：データレコード：介護給付費明細書：明細情報.
 */
final class LtcsBillingStatementAggregateRecord extends LtcsBillingStatementRecord
{
    private const UNIT_COST_FRACTION_DIGITS = 2;

    /**
     * {@link \Domain\Exchange\LtcsBillingStatementAggregateRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $officeCode 事業所番号
     * @param string $insurerNumber 証記載保険者番号
     * @param string $insNumber 被保険者番号
     * @param string $serviceDivisionCode サービス種類コード
     * @param int $serviceDays サービス実日数
     * @param int $plannedScore 計画単位数
     * @param int $managedScore 限度額管理対象単位数
     * @param int $unmanagedScore 限度額管理対象外単位数
     * @param \Domain\Billing\LtcsBillingStatementAggregateInsurance $insurance 保険請求情報
     * @param \Domain\Billing\LtcsBillingStatementAggregateSubsidy[] $subsidies 保険請求情報
     */
    public function __construct(
        Carbon $providedIn,
        string $officeCode,
        string $insurerNumber,
        string $insNumber,
        #[JsonIgnore] public readonly string $serviceDivisionCode,
        #[JsonIgnore] public readonly int $serviceDays,
        #[JsonIgnore] public readonly int $plannedScore,
        #[JsonIgnore] public readonly int $managedScore,
        #[JsonIgnore] public readonly int $unmanagedScore,
        #[JsonIgnore] public readonly LtcsBillingStatementAggregateInsurance $insurance,
        #[JsonIgnore] public readonly array $subsidies
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_AGGREGATE,
            providedIn: $providedIn,
            officeCode: $officeCode,
            insurerNumber: $insurerNumber,
            insNumber: $insNumber
        );
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @return \ScalikePHP\Seq&self[]
     */
    public static function from(LtcsBilling $billing, LtcsBillingBundle $bundle, LtcsBillingStatement $statement): Seq
    {
        return Seq::fromArray($statement->aggregates)->map(
            fn (LtcsBillingStatementAggregate $aggregate): self => new self(
                providedIn: $bundle->providedIn,
                officeCode: $billing->office->code,
                insurerNumber: $statement->insurerNumber,
                insNumber: $statement->user->insNumber,
                serviceDivisionCode: $aggregate->serviceDivisionCode->value(),
                serviceDays: $aggregate->serviceDays,
                plannedScore: $aggregate->plannedScore,
                managedScore: $aggregate->managedScore,
                unmanagedScore: $aggregate->unmanagedScore,
                insurance: $aggregate->insurance,
                subsidies: $aggregate->subsidies,
            )
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // サービス種類コード
            $this->serviceDivisionCode,
            // サービス実日数
            $this->serviceDays,
            // 計画単位数
            $this->plannedScore,
            // 限度額管理対象単位数
            $this->managedScore,
            // 限度額管理対象外単位数
            $this->unmanagedScore,
            // 短期入所計画日数
            self::UNUSED,
            // 短期入所実日数
            self::UNUSED,
            // 保険：単位数合計
            $this->insurance->totalScore,
            // 保険：単位数単価
            $this->insurance->unitCost->toInt(self::UNIT_COST_FRACTION_DIGITS),
            // 保険：請求額
            $this->insurance->claimAmount,
            // 保険：利用者負担額
            $this->insurance->copayAmount,
            // 公費1：単位数合計
            $this->subsidies[0]->totalScore,
            // 公費1：請求額
            $this->subsidies[0]->claimAmount,
            // 公費1：本人負担額
            $this->subsidies[0]->copayAmount,
            // 公費2：単位数合計
            $this->subsidies[1]->totalScore,
            // 公費2：請求額
            $this->subsidies[1]->claimAmount,
            // 公費2：本人負担額
            $this->subsidies[1]->copayAmount,
            // 公費3：単位数合計
            $this->subsidies[2]->totalScore,
            // 公費3：請求額
            $this->subsidies[2]->claimAmount,
            // 公費3：本人負担額
            $this->subsidies[2]->copayAmount,
            // 保険分出来高医療費：単位数合計
            self::UNUSED,
            // 保険分出来高医療費：請求額
            self::UNUSED,
            // 保険分出来高医療費：出来高医療費利用者負担額
            self::UNUSED,
            // 公費1分出来高医療費：単位数合計
            self::UNUSED,
            // 公費1分出来高医療費：請求額
            self::UNUSED,
            // 公費1分出来高医療費：出来高医療費本人負担額
            self::UNUSED,
            // 公費2分出来高医療費：単位数合計
            self::UNUSED,
            // 公費2分出来高医療費：請求額
            self::UNUSED,
            // 公費2分出来高医療費：出来高医療費本人負担額
            self::UNUSED,
            // 公費3分出来高医療費：単位数合計
            self::UNUSED,
            // 公費3分出来高医療費：請求額
            self::UNUSED,
            // 公費3分出来高医療費：出来高医療費本人負担額
            self::UNUSED,
        ];
    }
}
