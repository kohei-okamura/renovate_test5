<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Closure;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Job\CreateJobUseCase;

/**
 * 介護保険サービス：請求状態更新ユースケース実装.
 */
class UpdateLtcsBillingStatusInteractor implements UpdateLtcsBillingStatusUseCase
{
    use Logging;

    private CreateJobUseCase $createJobUseCase;
    private LtcsBillingRepository $repository;
    private GetLtcsBillingInfoUseCase $getInfoUseCase;
    private LookupLtcsBillingUseCase $lookupUseCase;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \Domain\Billing\LtcsBillingRepository $repository
     * @param \UseCase\Billing\GetLtcsBillingInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupUseCase
     * @param \Domain\TransactionManagerFactory $factory
     * @param \UseCase\Job\CreateJobUseCase $createJobUseCase
     */
    public function __construct(
        LtcsBillingRepository $repository,
        GetLtcsBillingInfoUseCase $getInfoUseCase,
        LookupLtcsBillingUseCase $lookupUseCase,
        TransactionManagerFactory $factory,
        CreateJobUseCase $createJobUseCase
    ) {
        $this->repository = $repository;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->transaction = $factory->factory($repository);
        $this->createJobUseCase = $createJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, LtcsBillingStatus $status, Closure $dispatchClosure): array
    {
        $entity = $this->lookupEntity($context, $id);

        $updatedEntity = $entity->copy([
            'status' => $status,
            'updatedAt' => Carbon::now(),
        ]);

        $x = $this->transaction->run(fn (): LtcsBilling => $this->repository->store($updatedEntity));

        $this->logger()->info(
            '介護保険サービス：請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        $response = $this->getInfoUseCase->handle($context, $id);

        if ($status === LtcsBillingStatus::fixed()) {
            $job = $this->createJobUseCase->handle($context, $dispatchClosure);
            return compact('job') + $response;
        } else {
            return $response;
        }
    }

    /**
     * 介護保険サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Billing\LtcsBilling
     */
    private function lookupEntity(Context $context, int $id): LtcsBilling
    {
        return $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBilling({$id}) not found");
            });
    }
}
