<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Closure;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Job\CreateJobUseCase;

/**
 * 障害福祉サービス：請求 状態更新ユースケース実装.
 */
class UpdateDwsBillingStatusInteractor implements UpdateDwsBillingStatusUseCase
{
    use Logging;

    private CreateJobUseCase $createJobUseCase;
    private DwsBillingRepository $repository;
    private GetDwsBillingInfoUseCase $getBillingInfoUseCase;
    private LookupDwsBillingUseCase $lookupUseCase;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \Domain\Billing\DwsBillingRepository $repository
     * @param \UseCase\Billing\GetDwsBillingInfoUseCase $getBillingInfoUseCase
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupUseCase
     * @param TransactionManagerFactory $factory
     * @param \UseCase\Job\CreateJobUseCase $createJobUseCase
     */
    public function __construct(
        DwsBillingRepository $repository,
        GetDwsBillingInfoUseCase $getBillingInfoUseCase,
        LookupDwsBillingUseCase $lookupUseCase,
        TransactionManagerFactory $factory,
        CreateJobUseCase $createJobUseCase
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->getBillingInfoUseCase = $getBillingInfoUseCase;
        $this->transaction = $factory->factory($repository);
        $this->createJobUseCase = $createJobUseCase;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, int $id, DwsBillingStatus $status, Closure $dispatchClosure): array
    {
        /** @var \Domain\Billing\DwsBilling $entity */
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBilling({$id}) not found.");
            });

        $updatedEntity = $entity->copy([
            'status' => $status,
            'updatedAt' => Carbon::now(),
        ]);

        $x = $this->transaction->run(fn (): DwsBilling => $this->repository->store($updatedEntity));

        $this->logger()->info(
            '障害福祉サービス：請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        $response = $this->getBillingInfoUseCase->handle($context, $id);

        if ($status === DwsBillingStatus::fixed()) {
            $job = $this->createJobUseCase->handle($context, $dispatchClosure);
            return $response + compact('job');
        } else {
            return $response;
        }
    }
}
