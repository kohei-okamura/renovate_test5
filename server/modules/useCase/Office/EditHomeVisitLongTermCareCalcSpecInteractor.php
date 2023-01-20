<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\ProvisionReport\FindLtcsProvisionReportUseCase;

/**
 * 事業所算定情報（介保・訪問介護）編集実装.
 */
final class EditHomeVisitLongTermCareCalcSpecInteractor implements EditHomeVisitLongTermCareCalcSpecUseCase
{
    use Logging;

    private FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase;
    private LookupHomeVisitLongTermCareCalcSpecUseCase $lookupHomeVisitLongTermCareCalcSpecUseCase;
    private LookupOfficeUseCase $lookupOfficeUseCase;
    private HomeVisitLongTermCareCalcSpecRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\ProvisionReport\FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpecRepository $repository
     * @param \UseCase\Office\LookupHomeVisitLongTermCareCalcSpecUseCase $lookupHomeVisitLongTermCareCalcSpecUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase,
        HomeVisitLongTermCareCalcSpecRepository $repository,
        LookupHomeVisitLongTermCareCalcSpecUseCase $lookupHomeVisitLongTermCareCalcSpecUseCase,
        LookupOfficeUseCase $lookupOfficeUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->findLtcsProvisionReportUseCase = $findLtcsProvisionReportUseCase;
        $this->lookupHomeVisitLongTermCareCalcSpecUseCase = $lookupHomeVisitLongTermCareCalcSpecUseCase;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->Factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, int $id, array $values): array
    {
        $homeVisitLongTermCareCalcSpec = $this->transaction->run(function () use ($context, $officeId, $id, $values): HomeVisitLongTermCareCalcSpec {
            $entity = $this->lookupHomeVisitLongTermCareCalcSpecUseCase
                ->handle($context, [Permission::updateInternalOffices()], $officeId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("HomeVisitLongTermCareCalcSpec({$id}) not found");
                });
            return $this->repository->store($entity->copy($values + [
                'version' => $entity->version + 1,
                'updatedAt' => Carbon::now(),
            ]));
        });
        $filterParams = [
            'officeId' => $officeId,
            'provideInForBetween' => $homeVisitLongTermCareCalcSpec->period,
        ];
        $provisionReportCount = $this->findLtcsProvisionReportUseCase->handle(
            $context,
            Permission::updateInternalOffices(),
            $filterParams,
            ['all' => true],
        )
            ->list
            ->count();
        $this->logger()->info(
            '事業所算定情報（介保・訪問介護）が更新されました',
            ['id' => $homeVisitLongTermCareCalcSpec->id] + $context->logContext()
        );
        return compact('homeVisitLongTermCareCalcSpec', 'provisionReportCount');
    }
}
