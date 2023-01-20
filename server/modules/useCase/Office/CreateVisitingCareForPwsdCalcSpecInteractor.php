<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * 事業所算定情報（障害・重度訪問介護）登録実装.
 */
final class CreateVisitingCareForPwsdCalcSpecInteractor implements CreateVisitingCareForPwsdCalcSpecUseCase
{
    use Logging;

    private EnsureOfficeUseCase $ensureOfficeUseCase;
    private TransactionManager $transaction;
    private VisitingCareForPwsdCalcSpecRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     * @param \Domain\Office\VisitingCareForPwsdCalcSpecRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureOfficeUseCase $ensureOfficeUseCase,
        VisitingCareForPwsdCalcSpecRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec): VisitingCareForPwsdCalcSpec
    {
        $x = $this->transaction->run(function () use ($context, $officeId, $visitingCareForPwsdCalcSpec): VisitingCareForPwsdCalcSpec {
            $this->ensureOfficeUseCase->handle($context, [Permission::createInternalOffices()], $officeId);
            return $this->repository->store($visitingCareForPwsdCalcSpec);
        });
        $this->logger()->info(
            '事業所算定情報（障害・重度訪問介護）が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
