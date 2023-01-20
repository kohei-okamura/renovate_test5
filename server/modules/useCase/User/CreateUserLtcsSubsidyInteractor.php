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
use Domain\User\UserLtcsSubsidy;
use Domain\User\UserLtcsSubsidyRepository;
use Lib\Logging;

/**
 * 公費情報登録実装.
 */
final class CreateUserLtcsSubsidyInteractor implements CreateUserLtcsSubsidyUseCase
{
    use Logging;

    private EnsureUserUseCase $ensureUserUseCase;
    private UserLtcsSubsidyRepository $userLtcsSubsidyRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\User\UserLtcsSubsidyRepository $userLtcsSubsidyRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        UserLtcsSubsidyRepository $userLtcsSubsidyRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->userLtcsSubsidyRepository = $userLtcsSubsidyRepository;
        $this->transaction = $transactionManagerFactory->factory($userLtcsSubsidyRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, UserLtcsSubsidy $userLtcsSubsidy): UserLtcsSubsidy
    {
        $this->ensureUserUseCase->handle($context, Permission::createUserLtcsSubsidies(), $userId);
        $x = $this->transaction->run(function () use ($userId, $userLtcsSubsidy): UserLtcsSubsidy {
            $values = [
                'userId' => $userId,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            return $this->userLtcsSubsidyRepository->store($userLtcsSubsidy->copy($values));
        });
        $this->logger()->info(
            '公費情報が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
