<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 介護保険サービス：請求編集ユースケース実装.
 */
class EditLtcsBillingInteractor implements EditLtcsBillingUseCase
{
    use Logging;

    private LookupLtcsBillingUseCase $lookupUseCase;
    private LtcsBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupUseCase
     * @param \Domain\Billing\LtcsBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LookupLtcsBillingUseCase $lookupUseCase,
        LtcsBillingRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, int $id, array $values): LtcsBilling
    {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBilling({$id}) not found.");
            });

        /** @var \Domain\Billing\LtcsBilling $x */
        $x = $this->transaction->run(fn (): LtcsBilling => $this->repository->store(
            $entity->copy($values + ['updatedAt' => Carbon::now()])
        ));
        $this->logger()->info(
            '介護保険サービス：請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
