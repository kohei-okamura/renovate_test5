<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoicePdf;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatementPdf;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Context\Context;
use Domain\ServiceCode\ServiceCode;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase;

/**
 * 障害福祉サービス：介護給付費請求書・明細書PDFパラメータ組み立てユースケース実装.
 */
class BuildDwsBillingInvoicePdfParamInteractor implements BuildDwsBillingInvoicePdfParamUseCase
{
    private DwsBillingInvoiceRepository $invoiceRepository;
    private DwsBillingStatementRepository $statementRepository;
    private ResolveDwsNameFromServiceCodesUseCase $resolveDwsNameFromServiceCodesUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingInvoicePdfParamInteractor} Constructor.
     *
     * @param \Domain\Billing\DwsBillingInvoiceRepository $invoiceRepository
     * @param \Domain\Billing\DwsBillingStatementRepository $statementRepository
     * @param \UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase $resolveDwsNameFromServiceCodesUseCase
     */
    public function __construct(
        DwsBillingInvoiceRepository $invoiceRepository,
        DwsBillingStatementRepository $statementRepository,
        ResolveDwsNameFromServiceCodesUseCase $resolveDwsNameFromServiceCodesUseCase
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->statementRepository = $statementRepository;
        $this->resolveDwsNameFromServiceCodesUseCase = $resolveDwsNameFromServiceCodesUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array
    {
        return [
            'bundles' => $bundles->map(fn (DwsBillingBundle $bundle): array => [
                'invoices' => $this->lookupInvoices($bundle->id)
                    ->map(
                        fn (DwsBillingInvoice $invoice): DwsBillingInvoicePdf => DwsBillingInvoicePdf::from(
                            $billing,
                            $bundle,
                            $invoice
                        )
                    ),
                'statements' => $this->lookupStatements($bundle->id)
                    ->map(
                        function (DwsBillingStatement $statement) use ($billing, $bundle, $context): DwsBillingStatementPdf {
                            $serviceCodeMap = $this->getServiceCodeMap($context, $statement);
                            return DwsBillingStatementPdf::from(
                                $billing,
                                $bundle,
                                $statement,
                                $serviceCodeMap
                            );
                        }
                    ),
            ]),
        ];
    }

    /**
     * 障害福祉サービス：請求書の一覧を取得する.
     *
     * @param int $dwsBillingBundleId
     * @return \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function lookupInvoices(int $dwsBillingBundleId): Seq
    {
        return $this->invoiceRepository
            ->lookupByBundleId($dwsBillingBundleId)
            ->values()
            ->flatten();
    }

    /**
     * 障害福祉サービス：明細書の一覧を取得する.
     *
     * @param int $dwsBillingBundleId
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq
     */
    private function lookupStatements(int $dwsBillingBundleId): Seq
    {
        return $this->statementRepository
            ->lookupByBundleId($dwsBillingBundleId)
            ->values()
            ->flatten();
    }

    /**
     * サービスコード => 辞書エントリ の Map を生成する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param Context $context
     * @return \ScalikePHP\Map
     */
    private function getServiceCodeMap(Context $context, DwsBillingStatement $statement): Map
    {
        $serviceCodes = Seq::fromArray($statement->items)
            ->filter(
                fn (DwsBillingStatementItem $item): bool => $item->serviceCode->serviceDivisionCode === DwsServiceDivisionCode::homeHelpService()->value()
                    || $item->serviceCode->serviceDivisionCode === DwsServiceDivisionCode::visitingCareForPwsd()->value()
            )
            ->map(fn (DwsBillingStatementItem $item): ServiceCode => $item->serviceCode);

        return $this->resolveDwsNameFromServiceCodesUseCase
            ->handle($context, $serviceCodes->computed());
    }
}
