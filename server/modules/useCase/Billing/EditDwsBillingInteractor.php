<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス：請求編集ユースケース実装.
 */
class EditDwsBillingInteractor implements EditDwsBillingUseCase
{
    use Logging;

    private LookupDwsBillingUseCase $lookupUseCase;
    private DwsBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupUseCase
     * @param \Domain\Billing\DwsBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LookupDwsBillingUseCase $lookupUseCase,
        DwsBillingRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, int $id, array $values): DwsBilling
    {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBilling({$id}) not found.");
            });

        /** @var \Domain\Billing\DwsBilling $x */
        $x = $this->transaction->run(fn (): DwsBilling => $this->repository->store(
            $entity->copy($values + ['updatedAt' => Carbon::now()])
        ));
        $this->logger()->info(
            '障害福祉サービス：請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
