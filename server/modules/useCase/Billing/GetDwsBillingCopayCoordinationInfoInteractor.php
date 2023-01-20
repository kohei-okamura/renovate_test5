<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * 利用者負担上限額管理結果票取得ユースケース実装.
 */
final class GetDwsBillingCopayCoordinationInfoInteractor implements GetDwsBillingCopayCoordinationInfoUseCase
{
    private LookupDwsBillingUseCase $lookupDwsBillingUseCase;
    private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase;
    private LookupDwsBillingCopayCoordinationUseCase $lookupDwsBillingCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\GetDwsBillingCopayCoordinationInfoInteractor} constructor.
     *
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupDwsBillingUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase
     * @param \UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase $lookupDwsBillingCopayCoordinationUseCase
     */
    public function __construct(
        LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        LookupDwsBillingCopayCoordinationUseCase $lookupDwsBillingCopayCoordinationUseCase
    ) {
        $this->lookupDwsBillingUseCase = $lookupDwsBillingUseCase;
        $this->lookupDwsBillingBundleUseCase = $lookupDwsBillingBundleUseCase;
        $this->lookupDwsBillingCopayCoordinationUseCase = $lookupDwsBillingCopayCoordinationUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $dwsBillingCopayCoordinationId
    ): array {
        $billing = $this->lookupDwsBillingUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingId): void {
                throw new NotFoundException("DwsBilling({$dwsBillingId}) not found.");
            });

        $bundle = $this->lookupDwsBillingBundleUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId, $dwsBillingBundleId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingBundleId): void {
                throw new NotFoundException("DwsBillingBundle({$dwsBillingBundleId}) not found.");
            });

        $copayCoordination = $this->lookupDwsBillingCopayCoordinationUseCase
            ->handle(
                $context,
                Permission::viewBillings(),
                $dwsBillingId,
                $dwsBillingBundleId,
                $dwsBillingCopayCoordinationId
            )
            ->headOption()
            ->getOrElse(function () use ($dwsBillingCopayCoordinationId): void {
                throw new NotFoundException(
                    "DwsBillingCopayCoordination({$dwsBillingCopayCoordinationId}) not found."
                );
            });

        return compact('billing', 'bundle', 'copayCoordination');
    }
}
