<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use UseCase\User\EnsureUserUseCase;

/**
 * 介護保険被保険者証登録実装.
 */
final class CreateLtcsInsCardInteractor implements CreateLtcsInsCardUseCase
{
    use Logging;

    private LtcsInsCardRepository $repository;
    private TransactionManager $transaction;
    private EnsureUserUseCase $ensureUserUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\LtcsInsCard\LtcsInsCardRepository $repository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        LtcsInsCardRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, LtcsInsCard $ltcsInsCard): LtcsInsCard
    {
        $this->ensureUserUseCase->handle($context, Permission::createLtcsInsCards(), $userId);

        $x = $this->transaction->run(function () use ($userId, $ltcsInsCard): LtcsInsCard {
            $values = [
                'userId' => $userId,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            return $this->repository->store($ltcsInsCard->copy($values));
        });
        $this->logger()->info(
            '介護保険被保険者証が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
