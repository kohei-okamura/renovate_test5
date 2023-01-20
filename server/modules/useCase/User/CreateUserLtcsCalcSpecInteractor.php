<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\UserLtcsCalcSpec;
use Domain\User\UserLtcsCalcSpecRepository;
use Lib\Logging;

/**
 * 介護保険サービス：利用者別算定情報登録ユースケース実装.
 */
class CreateUserLtcsCalcSpecInteractor implements CreateUserLtcsCalcSpecUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\TransactionManagerFactory $factory
     * @param \Domain\User\UserLtcsCalcSpecRepository $repository
     */
    public function __construct(
        private EnsureUserUseCase $ensureUserUseCase,
        private TransactionManagerFactory $factory,
        private UserLtcsCalcSpecRepository $repository
    ) {
        $this->transaction = $this->factory->factory($this->repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, UserLtcsCalcSpec $calcSpec): UserLtcsCalcSpec
    {
        $this->ensureUserUseCase->handle($context, Permission::createUserLtcsCalcSpecs(), $userId);

        $x = $this->transaction->run(fn (): UserLtcsCalcSpec => $this->repository->store($calcSpec));
        $this->logger()->info(
            '介護保険サービス：利用者別算定情報が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
