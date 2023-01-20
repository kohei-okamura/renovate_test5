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
 * 介護保険サービス：明細書取得ユースケース実装.
 */
final class GetLtcsBillingStatementInfoInteractor implements GetLtcsBillingStatementInfoUseCase
{
    private LookupLtcsBillingUseCase $lookupBillingUseCase;
    private LookupLtcsBillingBundleUseCase $lookupBillingBundleUseCase;
    private LookupLtcsBillingStatementUseCase $lookupBillingStatementUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupBillingUseCase
     * @param \UseCase\Billing\LookupLtcsBillingBundleUseCase $lookupBillingBundleUseCase
     * @param \UseCase\Billing\LookupLtcsBillingStatementUseCase $lookupBillingStatementUseCase
     */
    public function __construct(
        LookupLtcsBillingUseCase $lookupBillingUseCase,
        LookupLtcsBillingBundleUseCase $lookupBillingBundleUseCase,
        LookupLtcsBillingStatementUseCase $lookupBillingStatementUseCase
    ) {
        $this->lookupBillingUseCase = $lookupBillingUseCase;
        $this->lookupBillingBundleUseCase = $lookupBillingBundleUseCase;
        $this->lookupBillingStatementUseCase = $lookupBillingStatementUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, int $bundleId, int $id): array
    {
        $billing = $this->lookupBillingUseCase
            ->handle($context, Permission::viewBillings(), $billingId)
            ->headOption()
            ->getOrElse(function () use ($billingId): void {
                throw new NotFoundException("LtcsBilling({$billingId}) not found.");
            });

        $bundle = $this->lookupBillingBundleUseCase
            ->handle($context, Permission::viewBillings(), $billing, $bundleId)
            ->headOption()
            ->getOrElse(function () use ($bundleId): void {
                throw new NotFoundException("LtcsBillingBundle({$bundleId}) not found.");
            });

        $statement = $this->lookupBillingStatementUseCase
            ->handle($context, Permission::viewBillings(), $billing, $bundle, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBillingStatement({$id}) not found.");
            });

        return compact('billing', 'bundle', 'statement');
    }
}
