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
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 介護保険被保険者証編集実装.
 */
final class EditLtcsInsCardInteractor implements EditLtcsInsCardUseCase
{
    use Logging;

    private LookupLtcsInsCardUseCase $lookupUseCase;
    private LtcsInsCardRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\LtcsInsCard\LookupLtcsInsCardUseCase $lookupUseCase
     * @param \Domain\LtcsInsCard\LtcsInsCardRepository $repository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupLtcsInsCardUseCase $lookupUseCase,
        LtcsInsCardRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): LtcsInsCard
    {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateLtcsInsCards(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsInsCard({$id}) not found");
            });

        $x = $this->transaction->run(fn (): LtcsInsCard => $this->repository->store(
            $entity->copy(
                $values + [
                    'version' => $entity->version + 1,
                    'updatedAt' => Carbon::now(),
                ]
            )
        ));

        $this->logger()->info(
            '介護保険被保険者証が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
