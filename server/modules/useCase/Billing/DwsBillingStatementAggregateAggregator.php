<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatementAggregate;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書：集計を更に集計する各種共通処理.
 */
trait DwsBillingStatementAggregateAggregator
{
    /**
     * 《給付単位数》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return int
     */
    protected function aggregateSubtotalScore(Seq $aggregates): int
    {
        return $aggregates->map(fn (DwsBillingStatementAggregate $x): int => $x->subtotalScore)->sum();
    }

    /**
     * 《総費用額》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return int
     */
    protected function aggregateSubtotalFee(Seq $aggregates): int
    {
        return $aggregates->map(fn (DwsBillingStatementAggregate $x): int => $x->subtotalFee)->sum();
    }

    /**
     * 《上限月額調整》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return int
     */
    protected function aggregateCappedCopay(Seq $aggregates): int
    {
        return $aggregates->map(fn (DwsBillingStatementAggregate $x): int => $x->cappedCopay)->sum();
    }

    /**
     * 《調整後利用者負担額》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return null|int
     */
    protected function aggregateAdjustedCopay(Seq $aggregates): ?int
    {
        return $aggregates->fold(null, function (?int $z, DwsBillingStatementAggregate $x): ?int {
            if ($x->adjustedCopay === null) {
                return $z;
            } elseif ($z === null) {
                return $x->adjustedCopay;
            } else {
                return $z + $x->adjustedCopay;
            }
        });
    }

    /**
     * 《上限管理後利用者負担額》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return null|int
     */
    protected function aggregateCoordinatedCopay(Seq $aggregates): ?int
    {
        return $aggregates->fold(null, function (?int $z, DwsBillingStatementAggregate $x): ?int {
            if ($x->coordinatedCopay === null) {
                return $z;
            } elseif ($z === null) {
                return $x->coordinatedCopay;
            } else {
                return $z + $x->coordinatedCopay;
            }
        });
    }

    /**
     * 《決定利用者負担額》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return int
     */
    protected function aggregateSubtotalCopay(Seq $aggregates): int
    {
        return $aggregates->map(fn (DwsBillingStatementAggregate $x): int => $x->subtotalCopay)->sum();
    }

    /**
     * 《請求額：給付費》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]|\ScalikePHP\Seq $aggregates
     * @return int
     */
    protected function aggregateSubtotalBenefit(Seq $aggregates): int
    {
        return $aggregates->map(fn (DwsBillingStatementAggregate $x): int => $x->subtotalBenefit)->sum();
    }

    /**
     * 《自治体助成分請求額》の合計値を集計する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate[]&\ScalikePHP\Seq $aggregates
     * @return null|int
     */
    protected function aggregateSubtotalSubsidy(Seq $aggregates): ?int
    {
        return $aggregates->forAll(fn (DwsBillingStatementAggregate $x): bool => $x->subtotalSubsidy === null)
            ? null
            : $aggregates->map(fn (DwsBillingStatementAggregate $x): int => $x->subtotalSubsidy ?? 0)->sum();
    }
}
