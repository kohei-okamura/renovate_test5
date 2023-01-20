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
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 公費情報更新実装
 */
final class EditUserLtcsSubsidyInteractor implements EditUserLtcsSubsidyUseCase
{
    use Logging;

    private LookupUserLtcsSubsidyUseCase $lookupUseCase;
    private UserLtcsSubsidyRepository $repository;
    private TransactionManager $transaction;

    /**
     * コンストラクタ
     *
     * @param \UseCase\User\LookupUserLtcsSubsidyUseCase $lookupUseCase
     * @param \Domain\User\UserLtcsSubsidyRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserLtcsSubsidyUseCase $lookupUseCase,
        UserLtcsSubsidyRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): UserLtcsSubsidy
    {
        $x = $this->transaction->run(function () use ($context, $userId, $id, $values): UserLtcsSubsidy {
            $entity = $this->lookupUseCase->handle($context, Permission::updateUserLtcsSubsidies(), $userId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("UserLtcsSubsidy({$id}) not found");
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
            '公費情報が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
