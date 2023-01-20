<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Context\Context;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 *障害福祉サービス受給者証削除実装.
 */
final class DeleteDwsCertificationInteractor implements DeleteDwsCertificationUseCase
{
    use Logging;

    private LookupDwsCertificationUseCase $lookupUseCase;
    private DwsCertificationRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\DwsCertification\LookupDwsCertificationUseCase $lookupUseCase
     * @param \Domain\DwsCertification\DwsCertificationRepository $dwsCertificationRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupDwsCertificationUseCase $lookupUseCase,
        DwsCertificationRepository $dwsCertificationRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $dwsCertificationRepository;
        $this->transaction = $transactionManagerFactory->factory($dwsCertificationRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id): void
    {
        $this->lookupUseCase
            ->handle($context, Permission::deleteDwsCertifications(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsCertification[{$id}] not found");
            });
        $this->transaction->run(function () use ($id): void {
            $this->repository->removeById($id);
        });
        $this->logger()->info(
            '障害福祉サービス受給者証が削除されました',
            ['id' => $id] + $context->logContext()
        );
    }
}
