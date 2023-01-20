<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 利用者負担上限額管理結果票 PDF パラメータ組み立てユースケース実装.
 */
final class BuildDwsBillingCopayCoordinationPdfParamInteractor implements BuildDwsBillingCopayCoordinationPdfParamUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamInteractor} constructor.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationFinder $copayCoordinationFinder
     */
    public function __construct(
        private DwsBillingCopayCoordinationFinder $copayCoordinationFinder
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array
    {
        return [
            'bundles' => $bundles->map(fn (DwsBillingBundle $bundle): array => [
                'copayCoordinations' => $this
                    ->findCopayCoordinations($billing->id, $bundle->id)
                    ->map(function (DwsBillingCopayCoordination $x) use ($bundle): DwsBillingCopayCoordinationPdf {
                        return DwsBillingCopayCoordinationPdf::from($bundle, $x);
                    }),
            ]),
        ];
    }

    /**
     * 利用者負担上限額管理結果票の一覧を取得する.
     *
     * @param int $billingId
     * @param int $bundleId
     * @return \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Seq
     */
    private function findCopayCoordinations(int $billingId, int $bundleId): Seq
    {
        $filterParams = [
            'dwsBillingId' => $billingId,
            'dwsBillingBundleId' => $bundleId,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->copayCoordinationFinder->find($filterParams, $paginationParams)->list;
    }
}
