<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use UseCase\File\GenerateFileNameUseCase;

/**
 * 利用者負担上限額管理結果票ダウンロード実装.
 */
final class DownloadDwsBillingCopayCoordinationInteractor implements DownloadDwsBillingCopayCoordinationUseCase
{
    private GenerateFileNameUseCase $generateFileNameUseCase;
    private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase;
    private LookupDwsBillingCopayCoordinationUseCase $lookupCopayCoordinationUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\File\GenerateFileNameUseCase $generateFileNameUseCase
     * @param \UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase $lookupCopayCoordinationUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase
     */
    public function __construct(
        GenerateFileNameUseCase $generateFileNameUseCase,
        LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        LookupDwsBillingCopayCoordinationUseCase $lookupCopayCoordinationUseCase
    ) {
        $this->generateFileNameUseCase = $generateFileNameUseCase;
        $this->lookupDwsBillingBundleUseCase = $lookupDwsBillingBundleUseCase;
        $this->lookupCopayCoordinationUseCase = $lookupCopayCoordinationUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, int $bundleId, int $copayCoordinationId): array
    {
        $bundle = $this->lookupBundle($context, $billingId, $bundleId);
        $copayCoordination = $this->lookupCopayCoordination($context, $billingId, $bundleId, $copayCoordinationId);
        $filename = $this->createName($copayCoordination, $bundle->providedIn);
        return [
            'filename' => $filename,
            'params' => [
                'bundles' => [
                    [
                        'copayCoordinations' => [
                            DwsBillingCopayCoordinationPdf::from($bundle, $copayCoordination),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * ダウンロード用のファイル名を生成する.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordination $billingCopayCoordination
     * @param \Domain\Common\Carbon $providedIn
     * @return string
     */
    private function createName(DwsBillingCopayCoordination $billingCopayCoordination, Carbon $providedIn): string
    {
        $placeholders = [
            'office' => $billingCopayCoordination->office->abbr,
            'providedIn' => $providedIn,
        ];
        return $this->generateFileNameUseCase->handle('dws_copay_coordination_pdf', $placeholders);
    }

    /**
     * 障害福祉サービス：請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $id
     * @return \Domain\Billing\DwsBillingBundle
     */
    private function lookupBundle(Context $context, int $billingId, int $id): DwsBillingBundle
    {
        return $this->lookupDwsBillingBundleUseCase
            ->handle($context, Permission::downloadBillings(), $billingId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingBundle({$id}) not found");
            });
    }

    /**
     * 利用者負担上限額管理結果票を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function lookupCopayCoordination(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id
    ): DwsBillingCopayCoordination {
        return $this->lookupCopayCoordinationUseCase
            ->handle($context, Permission::downloadBillings(), $billingId, $bundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingCopayCoordination({$id}) not found");
            });
    }
}
