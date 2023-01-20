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
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス受給者証編集実装.
 */
final class EditDwsCertificationInteractor implements EditDwsCertificationUseCase
{
    use Logging;

    private LookupDwsCertificationUseCase $lookupUseCase;
    private DwsCertificationRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\DwsCertification\LookupDwsCertificationUseCase $lookupUseCase
     * @param \Domain\DwsCertification\DwsCertificationRepository $repository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupDwsCertificationUseCase $lookupUseCase,
        DwsCertificationRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): DwsCertification
    {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateDwsCertifications(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsCertification({$id}) not found");
            });

        $x = $this->transaction->run(fn (): DwsCertification => $this->repository->store(
            $entity->copy(
                $values + [
                    'updatedAt' => Carbon::now(),
                    'version' => $entity->version + 1,
                ]
            )
        ));
        $this->logger()->info(
            '障害福祉サービス受給者証が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
