<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\UserLtcsCalcSpec;
use Domain\User\UserLtcsCalcSpecRepository;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 介護保険サービス：利用者別算定情報編集実装.
 */
class EditUserLtcsCalcSpecInteractor implements EditUserLtcsCalcSpecUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\LookupUserLtcsCalcSpecUseCase $lookupUseCase
     * @param \Domain\User\UserLtcsCalcSpecRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        private LookupUserLtcsCalcSpecUseCase $lookupUseCase,
        private UserLtcsCalcSpecRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): UserLtcsCalcSpec
    {
        $x = $this->transaction->run(function () use ($context, $userId, $id, $values): UserLtcsCalcSpec {
            $entity = $this->lookupUseCase->handle($context, Permission::updateUserLtcsCalcSpecs(), $userId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("UserLtcsCalcSpec({$id}) not found");
                });
            return $this->repository->store(
                $entity->copy(
                    $values + [
                        'version' => $entity->version + 1,
                        'updatedAt' => Carbon::now(),
                    ]
                )
            );
        });
        $this->logger()->info(
            '介護保険サービス：利用者別算定情報が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
