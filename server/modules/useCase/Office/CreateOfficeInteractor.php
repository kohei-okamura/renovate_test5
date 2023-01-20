<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * 事業所登録実装.
 */
final class CreateOfficeInteractor implements CreateOfficeUseCase
{
    use Logging;

    private OfficeRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        OfficeRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Office $office, callable $f): Office
    {
        $x = $this->transaction->run(function () use ($context, $office, $f): Office {
            $entity = $this->repository->store(
                $office->copy(['organizationId' => $context->organization->id])
            );
            $f($entity);
            return $entity;
        });
        $this->logger()->info(
            '事業所が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
