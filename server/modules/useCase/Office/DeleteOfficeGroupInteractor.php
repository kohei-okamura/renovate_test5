<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\OfficeGroupRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業所グループ削除実装.
 */
final class DeleteOfficeGroupInteractor implements DeleteOfficeGroupUseCase
{
    use Logging;

    private LookupOfficeGroupUseCase $lookupUseCase;
    private OfficeGroupRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \Domain\Office\OfficeGroupRepository $officeGroupRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param LookupOfficeGroupUseCase $lookupUseCase
     */
    public function __construct(
        LookupOfficeGroupUseCase $lookupUseCase,
        OfficeGroupRepository $officeGroupRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $officeGroupRepository;
        $this->transaction = $transactionManagerFactory->factory($officeGroupRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): void
    {
        $this->lookupUseCase->handle($context, $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("OfficeGroup[{$id}] not found");
        });
        $this->transaction->run(function () use ($id): void {
            $this->repository->removeById($id);
        });
        $this->logger()->info(
            '事業所グループが削除されました',
            ['id' => $id] + $context->logContext()
        );
    }
}
