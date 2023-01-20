<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業所算定情報（障害・重度訪問介護）編集実装.
 */
final class EditVisitingCareForPwsdCalcSpecInteractor implements EditVisitingCareForPwsdCalcSpecUseCase
{
    use Logging;

    private LookupVisitingCareForPwsdCalcSpecUseCase $lookupVisitingCareForPwsdCalcSpecUseCase;
    private VisitingCareForPwsdCalcSpecRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\LookupVisitingCareForPwsdCalcSpecUseCase $lookupVisitingCareForPwsdCalcSpecUseCase
     * @param \Domain\Office\VisitingCareForPwsdCalcSpecRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupVisitingCareForPwsdCalcSpecUseCase $lookupVisitingCareForPwsdCalcSpecUseCase,
        VisitingCareForPwsdCalcSpecRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupVisitingCareForPwsdCalcSpecUseCase = $lookupVisitingCareForPwsdCalcSpecUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, int $id, array $values): VisitingCareForPwsdCalcSpec
    {
        $x = $this->transaction->run(function () use ($context, $officeId, $id, $values): VisitingCareForPwsdCalcSpec {
            $entity = $this->lookupVisitingCareForPwsdCalcSpecUseCase
                ->handle($context, [Permission::updateInternalOffices()], $officeId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("VisitingCareForPwsdCalcSpec({$id}) not found");
                });
            return $this->repository->store($entity->copy($values + [
                'version' => $entity->version + 1,
                'updatedAt' => Carbon::now(),
            ]));
        });
        $this->logger()->info(
            '事業所算定情報（障害・重度訪問介護）が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
