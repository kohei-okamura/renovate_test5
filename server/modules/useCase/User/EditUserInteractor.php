<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Domain\User\UserRepository;
use Illuminate\Support\Arr;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 利用者編集実装.
 */
final class EditUserInteractor implements EditUserUseCase
{
    use Logging;

    private LookupUserUseCase $useCase;
    private UserRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\LookupUserUseCase $useCase
     * @param \Domain\User\UserRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserUseCase $useCase,
        UserRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->useCase = $useCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values, callable $f): User
    {
        $entity = $this->useCase->handle($context, Permission::updateUsers(), $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("User({$id}) not found");
        });

        $storedEntity = $this->transaction->run(function () use ($entity, $values, $f): User {
            if (Arr::exists($values, 'addr') && !$entity->addr->equals($values['addr'])) {
                // 住所更新がある場合
                $x = $this->repository->store($entity->copy(
                    $values + [
                        'location' => Location::create(['lat' => 0, 'lng' => 0]),
                        'updatedAt' => Carbon::now(),
                        'version' => $entity->version + 1,
                    ]
                ));
                $f($x);
                return $x;
            }
            return $this->repository->store($entity->copy(
                $values +
                [
                    'updatedAt' => Carbon::now(),
                    'version' => $entity->version + 1,
                ]
            ));
        });

        $this->logger()->info(
            '利用者情報が更新されました',
            ['id' => $storedEntity->id] + $context->logContext()
        );

        return $storedEntity;
    }
}
