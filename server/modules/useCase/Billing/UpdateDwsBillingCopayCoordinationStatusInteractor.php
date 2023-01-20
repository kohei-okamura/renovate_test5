<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス利用者負担上限額管理結果票状態更新ユースケース実装.
 */
final class UpdateDwsBillingCopayCoordinationStatusInteractor implements UpdateDwsBillingCopayCoordinationStatusUseCase
{
    use Logging;

    private DwsBillingCopayCoordinationRepository $repository;
    private GetDwsBillingCopayCoordinationInfoUseCase $getInfoUseCase;
    private LookupDwsBillingCopayCoordinationUseCase $lookupUseCase;
    private DwsBillingStatementFinder $statementFinder;
    private UpdateDwsBillingStatementCopayCoordinationUseCase $updateStatementCopayCoordinationUseCase;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationRepository $repository
     * @param \UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase $lookupUseCase
     * @param \Domain\Billing\DwsBillingStatementFinder $statementFinder
     * @param \UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase $updateStatementCopayCoordinationUseCase
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DwsBillingCopayCoordinationRepository $repository,
        GetDwsBillingCopayCoordinationInfoUseCase $getInfoUseCase,
        LookupDwsBillingCopayCoordinationUseCase $lookupUseCase,
        DwsBillingStatementFinder $statementFinder,
        UpdateDwsBillingStatementCopayCoordinationUseCase $updateStatementCopayCoordinationUseCase,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->statementFinder = $statementFinder;
        $this->updateStatementCopayCoordinationUseCase = $updateStatementCopayCoordinationUseCase;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        DwsBillingStatus $status
    ): array {
        /** @var \Domain\Billing\DwsBillingCopayCoordination $entity */
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $dwsBillingId, $dwsBillingBundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingCopayCoordination({$id}) not found.");
            });

        $updateEntity = $entity->copy([
            'status' => $status,
            'updatedAt' => Carbon::now(),
        ]);

        $statement = $this
            ->findStatement($dwsBillingBundleId, $entity->user->userId)
            ->headOption()
            ->getOrElse(function (): void {
                throw new NotFoundException('DwsBillingStatement not found.');
            });

        $x = $this->transaction->run(function () use (
            $context,
            $dwsBillingId,
            $dwsBillingBundleId,
            $statement,
            $updateEntity
        ): DwsBillingCopayCoordination {
            // 確定 or 未確定 以外の状態はここには来ないため考慮しない
            $values = $updateEntity->status === DwsBillingStatus::fixed()
                ? Option::some([
                    'result' => $updateEntity->result,
                    'amount' => $updateEntity->items[0]->subtotal->coordinatedCopay, // 自事業所の管理結果後利用者負担額を設定
                ])
                : Option::none();
            $storedEntity = $this->repository->store($updateEntity);
            $this->updateStatementCopayCoordinationUseCase->handle(
                $context,
                $dwsBillingId,
                $dwsBillingBundleId,
                $statement->id,
                $values
            );
            return $storedEntity;
        });

        $this->logger()->info(
            '利用者負担上限額管理結果票が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $this->getInfoUseCase->handle($context, $dwsBillingId, $dwsBillingBundleId, $id);
    }

    /**
     * 障害福祉サービス：明細書を取得する.
     *
     * @param int $bundleId
     * @param int $userId
     * @return \ScalikePHP\Seq
     */
    private function findStatement(int $bundleId, int $userId): Seq
    {
        $filterParams = [
            'dwsBillingBundleId' => $bundleId,
            'userId' => $userId,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->statementFinder->find($filterParams, $paginationParams)->list;
    }
}
