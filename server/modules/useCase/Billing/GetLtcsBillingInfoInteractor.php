<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleFinder;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * 介護保険サービス：請求取得ユースケース実装.
 */
class GetLtcsBillingInfoInteractor implements GetLtcsBillingInfoUseCase
{
    private LookupLtcsBillingUseCase $lookupBillingUseCase;
    private LtcsBillingBundleFinder $bundleFinder;
    private LtcsBillingStatementFinder $statementFinder;

    public function __construct(
        LookupLtcsBillingUseCase $lookupBillingUseCase,
        LtcsBillingBundleFinder $bundleFinder,
        LtcsBillingStatementFinder $statementFinder
    ) {
        $this->lookupBillingUseCase = $lookupBillingUseCase;
        $this->bundleFinder = $bundleFinder;
        $this->statementFinder = $statementFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): array
    {
        $billing = $this->lookupBillingUseCase
            ->handle($context, Permission::viewBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBilling({$id}) not found.");
            });

        $bundleSeq = $this->bundleFinder
            ->find(['billingId' => $id], ['all' => true, 'sortBy' => 'id'])
            ->list;
        $bundles = $bundleSeq->toArray();

        $bundleIds = $bundleSeq->map(fn (LtcsBillingBundle $x): int => $x->id)->toArray();

        $statements = $this->statementFinder
            ->find(compact('bundleIds'), ['all' => true, 'sortBy' => 'id'])
            ->list
            ->toArray();

        return compact('billing', 'bundles', 'statements');
    }
}
