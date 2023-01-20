<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoice as Invoice;
use Domain\Billing\DwsBillingInvoiceItem as InvoiceItem;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsBillingStatement as Statement;
use Domain\Billing\DwsBillingStatementAggregate as StatementAggregate;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求書組み立てユースケース実装.
 */
class BuildDwsBillingInvoiceInteractor implements BuildDwsBillingInvoiceUseCase
{
    use DwsBillingStatementAggregateAggregator;

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, DwsBillingBundle $bundle, Seq $statements): DwsBillingInvoice
    {
        $items = $this->generateItems($statements);

        $totalCount = $items->map(fn (InvoiceItem $x): int => $x->subtotalCount)->sum();
        $totalScore = $items->map(fn (InvoiceItem $x): int => $x->subtotalScore)->sum();
        $totalFee = $items->map(fn (InvoiceItem $x): int => $x->subtotalFee)->sum();
        $totalBenefit = $items->map(fn (InvoiceItem $x): int => $x->subtotalBenefit)->sum();
        $totalCopay = $items->map(fn (InvoiceItem $x): int => $x->subtotalCopay)->sum();
        $totalSubsidy = $items->map(fn (InvoiceItem $x): int => $x->subtotalSubsidy)->sum();

        return Invoice::create([
            'dwsBillingBundleId' => $bundle->id,
            'claimAmount' => $totalBenefit + $totalSubsidy,
            'dwsPayment' => Invoice::payment([
                'subtotalDetailCount' => $totalCount,
                'subtotalScore' => $totalScore,
                'subtotalFee' => $totalFee,
                'subtotalBenefit' => $totalBenefit,
                'subtotalCopay' => $totalCopay,
                'subtotalSubsidy' => $totalSubsidy,
            ]),
            'highCostDwsPayment' => Invoice::highCostPayment([
                'subtotalDetailCount' => 0,
                'subtotalFee' => 0,
                'subtotalBenefit' => 0,
            ]),
            'totalCount' => $totalCount,
            'totalScore' => $totalScore,
            'totalFee' => $totalFee,
            'totalBenefit' => $totalBenefit,
            'totalCopay' => $totalCopay,
            'totalSubsidy' => $totalSubsidy,
            'items' => [...$items],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * 障害福祉サービス：請求書：明細の一覧を生成する.
     *
     * @param \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements
     * @return \Domain\Billing\DwsBillingInvoiceItem[]|\ScalikePHP\Seq
     */
    private function generateItems(Seq $statements): Seq
    {
        return $statements
            ->flatMap(fn (Statement $x): iterable => $x->aggregates)
            ->groupBy(fn (StatementAggregate $x): string => $x->serviceDivisionCode->value())
            ->map(function (Seq $aggregates, $serviceDivisionCodeValue): array {
                $subtotalCopay = $this->aggregateSubtotalCopay($aggregates);
                $subtotalSubsidy = $this->aggregateSubtotalSubsidy($aggregates) ?? 0;
                $item = InvoiceItem::create([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::from((string)$serviceDivisionCodeValue),
                    'subtotalCount' => $aggregates->size(),
                    'subtotalScore' => $this->aggregateSubtotalScore($aggregates),
                    'subtotalFee' => $this->aggregateSubtotalFee($aggregates),
                    'subtotalBenefit' => $this->aggregateSubtotalBenefit($aggregates),
                    'subtotalCopay' => $subtotalCopay - $subtotalSubsidy,
                    'subtotalSubsidy' => $subtotalSubsidy,
                ]);
                return [$serviceDivisionCodeValue, $item];
            })
            ->values();
    }
}
