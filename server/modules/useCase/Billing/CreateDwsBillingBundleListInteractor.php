<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求単位一覧生成ユースケース実装.
 */
final class CreateDwsBillingBundleListInteractor implements CreateDwsBillingBundleListUseCase
{
    private BuildDwsBillingServiceDetailListUseCase $buildServiceDetailListUseCase;
    private DwsBillingBundleRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingBundleListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingServiceDetailListUseCase $buildServiceDetailListUseCase
     * @param \Domain\Billing\DwsBillingBundleRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        BuildDwsBillingServiceDetailListUseCase $buildServiceDetailListUseCase,
        DwsBillingBundleRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->buildServiceDetailListUseCase = $buildServiceDetailListUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Office $office,
        DwsBilling $billing,
        Carbon $providedIn,
        Seq $sources
    ): Seq {
        return $this->transaction->run(function () use ($context, $office, $billing, $providedIn, $sources): Seq {
            $attrs = [
                'dwsBillingId' => $billing->id,
                'providedIn' => $providedIn,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            return $this->buildServiceDetailListUseCase
                ->handle($context, $office, $providedIn, $sources)
                ->map(function (array $values) use ($attrs): DwsBillingBundle {
                    $bundle = DwsBillingBundle::create($attrs + $values);
                    return $this->repository->store($bundle);
                })
                ->computed();
        });
    }
}
