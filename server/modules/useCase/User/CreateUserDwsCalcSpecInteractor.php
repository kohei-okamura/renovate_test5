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
use Domain\User\UserDwsCalcSpec;
use Domain\User\UserDwsCalcSpecRepository;
use Lib\Logging;

/**
 * 障害福祉サービス：利用者別算定情報登録ユースケース実装.
 */
class CreateUserDwsCalcSpecInteractor implements CreateUserDwsCalcSpecUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\TransactionManagerFactory $factory
     * @param \Domain\User\UserDwsCalcSpecRepository $repository
     */
    public function __construct(
        private EnsureUserUseCase $ensureUserUseCase,
        private TransactionManagerFactory $factory,
        private UserDwsCalcSpecRepository $repository
    ) {
        $this->transaction = $this->factory->factory($this->repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, UserDwsCalcSpec $calcSpec): UserDwsCalcSpec
    {
        $this->ensureUserUseCase->handle($context, Permission::createUserDwsCalcSpecs(), $userId);

        $x = $this->transaction->run(fn (): UserDwsCalcSpec => $this->repository->store($calcSpec));
        $this->logger()->info(
            '障害福祉サービス：利用者別算定情報が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
