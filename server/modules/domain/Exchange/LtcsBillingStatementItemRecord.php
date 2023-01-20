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
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：伝送：データレコード：介護給付費明細書：明細情報.
 */
final class LtcsBillingStatementItemRecord extends LtcsBillingStatementRecord
{
    /**
     * {@link \Domain\Exchange\LtcsBillingStatementItemRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $officeCode 事業所番号
     * @param string $insurerNumber 証記載保険者番号
     * @param string $insNumber 被保険者番号
     * @param \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
     * @param int $unitScore 単位数
     * @param int $count 日数・回数
     * @param int $totalScore サービス単位数
     * @param string $note 摘要
     * @param \Domain\Billing\LtcsBillingStatementItemSubsidy[] $subsidies
     */
    public function __construct(
        Carbon $providedIn,
        string $officeCode,
        string $insurerNumber,
        string $insNumber,
        #[JsonIgnore] public readonly ServiceCode $serviceCode,
        #[JsonIgnore] public readonly int $unitScore,
        #[JsonIgnore] public readonly int $count,
        #[JsonIgnore] public readonly int $totalScore,
        #[JsonIgnore] public readonly string $note,
        #[JsonIgnore] public readonly array $subsidies
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_ITEM,
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
        return Seq::fromArray($statement->items)->map(fn (LtcsBillingStatementItem $item): self => new self(
            providedIn: $bundle->providedIn,
            officeCode: $billing->office->code,
            insurerNumber: $statement->insurerNumber,
            insNumber: $statement->user->insNumber,
            serviceCode: $item->serviceCode,
            unitScore: $item->unitScore,
            count: $item->count,
            totalScore: $item->totalScore,
            note: $item->note,
            subsidies: $item->subsidies,
        ));
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        assert(count($this->subsidies) === 3);
        return [
            ...parent::toArray($recordNumber),
            // サービス種類コード
            $this->serviceCode->serviceDivisionCode,
            // サービス項目コード
            $this->serviceCode->serviceCategoryCode,
            // 単位数
            $this->unitScore,
            // 日数・回数
            $this->count,
            // 公費1対象日数・回数
            $this->subsidies[0]->count,
            // 公費2対象日数・回数
            $this->subsidies[1]->count,
            // 公費3対象日数・回数
            $this->subsidies[2]->count,
            // サービス単位数
            $this->totalScore,
            // 公費1対象サービス単位数
            $this->subsidies[0]->totalScore,
            // 公費2対象サービス単位数
            $this->subsidies[1]->totalScore,
            // 公費3対象サービス単位数
            $this->subsidies[2]->totalScore,
            // 摘要
            $this->note,
        ];
    }
}
