<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Polite;

/**
 * 利用者請求：介護サービス利用明細書 PDF 明細 合計
 */
final class UserBillingStatementPdfAmount extends Polite
{
    /**
     * {@link \Domain\Billing\UserBillingStatementPdfAmount} constructor
     *
     * @param string $score 単位数
     * @param string $unitCost 単価
     * @param string $subtotalCost 小計
     * @param string $benefitAmount 介護給付額
     * @param string $copayWithTax 自己負担額（税込）
     */
    public function __construct(
        public readonly string $score,
        public readonly string $unitCost,
        public readonly string $subtotalCost,
        public readonly string $benefitAmount,
        public readonly string $copayWithTax
    ) {
    }

    /**
     * 利用者請求：介護サービス利用明細書 PDF 明細 合計ドメインモデル（障害）を生成する.
     *
     * @param \Domain\UserBilling\UserBillingDwsItem $item
     * @return static
     */
    public static function fromDws(UserBillingDwsItem $item): self
    {
        return new self(
            score: number_format($item->score),
            unitCost: sprintf('%.2f', $item->unitCost->toInt(2) / 100),
            subtotalCost: number_format($item->subtotalCost),
            benefitAmount: number_format($item->benefitAmount),
            copayWithTax: number_format($item->copayWithTax)
        );
    }

    /**
     * 利用者請求：介護サービス利用明細書 PDF 明細 合計ドメインモデル（介保）を生成する.
     *
     * @param \Domain\UserBilling\UserBillingLtcsItem $item
     * @return static
     */
    public static function fromLTcs(UserBillingLtcsItem $item): self
    {
        return new self(
            score: number_format($item->score),
            unitCost: sprintf('%.2f', $item->unitCost->toInt(2) / 100),
            subtotalCost: number_format($item->subtotalCost),
            benefitAmount: number_format($item->benefitAmount),
            copayWithTax: number_format($item->copayWithTax)
        );
    }

    /**
     * 空の利用者請求：介護サービス利用明細書 PDF 明細 合計ドメインモデルを生成する.
     *
     * @return \Domain\UserBilling\UserBillingStatementPdfAmount
     */
    public static function empty(): self
    {
        return new self(
            score: '0',
            unitCost: '0',
            subtotalCost: '0',
            benefitAmount: '0',
            copayWithTax: '0'
        );
    }
}
