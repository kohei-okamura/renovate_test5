<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use UseCase\ProvisionReport\FindLtcsProvisionReportUseCase;

/**
 * 事業所算定情報（介保・訪問介護）登録実装.
 */
final class CreateHomeVisitLongTermCareCalcSpecInteractor implements CreateHomeVisitLongTermCareCalcSpecUseCase
{
    use Logging;

    private EnsureOfficeUseCase $ensureOfficeUseCase;
    private FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase;
    private HomeVisitLongTermCareCalcSpecRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     * @param \UseCase\ProvisionReport\FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpecRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureOfficeUseCase $ensureOfficeUseCase,
        FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase,
        HomeVisitLongTermCareCalcSpecRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
        $this->findLtcsProvisionReportUseCase = $findLtcsProvisionReportUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, HomeVisitLongTermCareCalcSpec $homeVisitLongTermCareCalcSpec): array
    {
        $homeVisitLongTermCareCalcSpec = $this->transaction->run(
            function () use ($context, $officeId, $homeVisitLongTermCareCalcSpec): HomeVisitLongTermCareCalcSpec {
                $this->ensureOfficeUseCase->handle($context, [Permission::createInternalOffices()], $officeId);
                return $this->repository->store($homeVisitLongTermCareCalcSpec);
            }
        );
        $filterParams = [
            'officeId' => $officeId,
            'provideInForBetween' => $homeVisitLongTermCareCalcSpec->period,
        ];
        $provisionReportCount = $this->findLtcsProvisionReportUseCase
            ->handle(
                $context,
                Permission::createInternalOffices(),
                $filterParams,
                ['all' => true],
            )
            ->list
            ->count();
        $this->logger()->info(
            '事業所算定情報（介保・訪問介護）が登録されました',
            ['id' => $homeVisitLongTermCareCalcSpec->id] + $context->logContext()
        );
        return compact('homeVisitLongTermCareCalcSpec', 'provisionReportCount');
    }
}
