<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyRepository;
use Lib\Logging;

/**
 * 自治体助成情報登録実装.
 */
final class CreateUserDwsSubsidyInteractor implements CreateUserDwsSubsidyUseCase
{
    use Logging;

    private EnsureUserUseCase $ensureUserUseCase;
    private UserDwsSubsidyRepository $userDwsSubsidyRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\User\UserDwsSubsidyRepository $userDwsSubsidyRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        UserDwsSubsidyRepository $userDwsSubsidyRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->userDwsSubsidyRepository = $userDwsSubsidyRepository;
        $this->transaction = $transactionManagerFactory->factory($userDwsSubsidyRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, UserDwsSubsidy $userDwsSubsidy): UserDwsSubsidy
    {
        $this->ensureUserUseCase->handle($context, Permission::createUserDwsSubsidies(), $userId);
        $x = $this->transaction->run(function () use ($userId, $userDwsSubsidy): UserDwsSubsidy {
            $values = [
                'userId' => $userId,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            return $this->userDwsSubsidyRepository->store($userDwsSubsidy->copy($values));
        });
        $this->logger()->info(
            '自治体助成情報が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
