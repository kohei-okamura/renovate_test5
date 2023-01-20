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
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 自治体助成情報更新実装.
 */
final class EditUserDwsSubsidyInteractor implements EditUserDwsSubsidyUseCase
{
    use Logging;

    private LookupUserDwsSubsidyUseCase $lookupUseCase;
    private UserDwsSubsidyRepository $repository;
    private TransactionManager $transaction;

    /**
     * コンストラクタ
     *
     * @param \UseCase\User\LookupUserDwsSubsidyUseCase $lookupUseCase
     * @param \Domain\User\UserDwsSubsidyRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserDwsSubsidyUseCase $lookupUseCase,
        UserDwsSubsidyRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): UserDwsSubsidy
    {
        $x = $this->transaction->run(function () use ($context, $userId, $id, $values): UserDwsSubsidy {
            $entity = $this->lookupUseCase->handle($context, Permission::updateUserDwsSubsidies(), $userId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("UserDwsSubsidy({$id}) not found");
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
            '自治体助成情報が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
