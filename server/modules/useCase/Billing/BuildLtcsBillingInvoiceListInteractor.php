<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundle as Bundle;
use Domain\Billing\LtcsBillingInvoice as Invoice;
use Domain\Billing\LtcsBillingStatement as Statement;
use Domain\Billing\LtcsBillingStatementSubsidy as StatementSubsidy;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求書一覧組み立てユースケース実装.
 */
final class BuildLtcsBillingInvoiceListInteractor implements BuildLtcsBillingInvoiceListUseCase
{
    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBillingBundle $bundle, Seq $statements): Seq
    {
        $subsidies = Seq::from(...self::forSubsidies($bundle, $statements));
        $insurance = self::forInsurance($bundle, $statements, $subsidies);
        return Seq::from($insurance, ...$subsidies);
    }

    /**
     * 公費請求分の請求書を生成する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return \Domain\Billing\LtcsBillingInvoice[]&iterable
     */
    private static function forSubsidies(Bundle $bundle, Seq $statements): iterable
    {
        /** @var \ScalikePHP\Map|\UseCase\Billing\LtcsBillingInvoiceAggregator[] $data */
        $data = $statements->fold(
            [],
            function (array $z, Statement $x): array {
                Seq::from(...$x->subsidies)
                    ->filter(fn (StatementSubsidy $x): bool => $x->defrayerCategory !== null)
                    ->each(function (StatementSubsidy $subsidy) use (&$z): void {
                        $category = $subsidy->defrayerCategory->value();
                        $m = $z[$category] ?? self::aggregator();
                        $m->append($subsidy->totalScore, $subsidy->claimAmount, $subsidy->copayAmount);
                        $z[$category] = $m;
                    });
                return $z;
            }
        );
        foreach ($data as $category => $aggregator) {
            $defrayerCategory = DefrayerCategory::from($category);
            yield new Invoice(
                id: null,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                isSubsidy: true,
                defrayerCategory: $defrayerCategory,
                statementCount: $aggregator->statementCount,
                totalScore: $aggregator->totalScore,
                totalFee: self::computeTotalFee(
                    $statements->filter(fn (Statement $x): bool => $x->includesDefrayerCategory($defrayerCategory))
                ),
                insuranceAmount: 0,
                subsidyAmount: $aggregator->claimAmount,
                copayAmount: $aggregator->copayAmount,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            );
        }
    }

    /**
     * 保険請求分の請求書を生成する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @param \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq $subsidies
     * @return \Domain\Billing\LtcsBillingInvoice
     */
    private static function forInsurance(Bundle $bundle, Seq $statements, Seq $subsidies): Invoice
    {
        /** @var \UseCase\Billing\LtcsBillingInvoiceAggregator $aggregator */
        $aggregator = $statements->fold(
            self::aggregator(),
            fn (LtcsBillingInvoiceAggregator $z, Statement $x): LtcsBillingInvoiceAggregator => $z->append(
                $x->insurance->totalScore,
                $x->insurance->claimAmount,
                $x->insurance->copayAmount
            ),
        );
        return new Invoice(
            id: null,
            billingId: $bundle->billingId,
            bundleId: $bundle->id,
            isSubsidy: false,
            defrayerCategory: null,
            statementCount: $aggregator->statementCount,
            totalScore: $aggregator->totalScore,
            totalFee: self::computeTotalFee($statements),
            insuranceAmount: $aggregator->claimAmount,
            subsidyAmount: $subsidies->map(fn (Invoice $x): int => $x->subsidyAmount)->sum(),
            copayAmount: $aggregator->copayAmount,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    /**
     * 介護保険サービス：明細書の一覧から介護報酬総額を算出する.
     *
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return int
     */
    private static function computeTotalFee(Seq $statements): int
    {
        $f = fn (Statement $x): int => $x->insurance->claimAmount
            + $x->insurance->copayAmount
            + Seq::from(...$x->subsidies)
                ->map(fn (StatementSubsidy $x): int => $x->claimAmount + $x->copayAmount)
                ->sum();
        return $statements->map($f)->sum();
    }

    /**
     * {@link \UseCase\Billing\LtcsBillingInvoiceAggregator} を生成する.
     *
     * @return \UseCase\Billing\LtcsBillingInvoiceAggregator
     */
    private static function aggregator(): LtcsBillingInvoiceAggregator
    {
        return new class() implements LtcsBillingInvoiceAggregator {
            private int $statementCount = 0;
            private int $totalScore = 0;
            private int $claimAmount = 0;
            private int $copayAmount = 0;

            public function append(int $totalScore, int $claimAmount, int $copayAmount): LtcsBillingInvoiceAggregator
            {
                ++$this->statementCount;
                $this->totalScore += $totalScore;
                $this->claimAmount += $claimAmount;
                $this->copayAmount += $copayAmount;
                return $this;
            }

            public function __get(string $name): int
            {
                return $this->{$name};
            }
        };
    }
}
