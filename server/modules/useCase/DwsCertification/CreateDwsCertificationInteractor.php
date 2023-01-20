<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use UseCase\User\EnsureUserUseCase;

/**
 * 障害福祉サービス受給者証登録実装.
 */
final class CreateDwsCertificationInteractor implements CreateDwsCertificationUseCase
{
    use Logging;

    private EnsureUserUseCase $ensureUserUseCase;
    private DwsCertificationRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\DwsCertification\DwsCertificationRepository $repository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        DwsCertificationRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, DwsCertification $entity): DwsCertification
    {
        $this->ensureUserUseCase->handle($context, Permission::createDwsCertifications(), $userId);

        $values = [
            'userId' => $userId,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];

        $x = $this->transaction->run(fn (): DwsCertification => $this->repository->store($entity->copy($values)));
        $this->logger()->info(
            '障害福祉サービス受給者証が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
