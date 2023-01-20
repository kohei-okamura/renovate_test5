<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 介護保険サービス：明細書状態更新ユースケース実装.
 */
final class UpdateLtcsBillingStatementStatusInteractor implements UpdateLtcsBillingStatementStatusUseCase
{
    use Logging;

    private LtcsBillingStatementRepository $repository;
    private EnsureLtcsBillingBundleUseCase $ensureUseCase;
    private GetLtcsBillingStatementInfoUseCase $getInfoUseCase;
    private LookupLtcsBillingStatementUseCase $lookupUseCase;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingStatementStatusInteractor} constructor.
     *
     * @param \Domain\Billing\LtcsBillingStatementRepository $repository
     * @param \UseCase\Billing\EnsureLtcsBillingBundleUseCase $ensureUseCase
     * @param \UseCase\Billing\GetLtcsBillingStatementInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupLtcsBillingStatementUseCase $lookupUseCase
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LtcsBillingStatementRepository $repository,
        EnsureLtcsBillingBundleUseCase $ensureUseCase,
        GetLtcsBillingStatementInfoUseCase $getInfoUseCase,
        LookupLtcsBillingStatementUseCase $lookupUseCase,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->ensureUseCase = $ensureUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id,
        LtcsBillingStatus $status,
        callable $f
    ): array {
        $statement = $this->lookupStatement($context, $billingId, $bundleId, $id)->copy([
            'status' => $status,
            'updatedAt' => Carbon::now(),
        ]);

        $x = $this->transaction->run(function () use ($statement): LtcsBillingStatement {
            return $this->repository->store($statement);
        });

        $this->logger()->info(
            '介護保険サービス：明細書が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        $info = $this->getInfoUseCase->handle($context, $billingId, $bundleId, $id);

        $f($info['billing']);

        return $info;
    }

    /**
     * 介護保険サービス：請求：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @return \Domain\Billing\LtcsBillingStatement
     */
    private function lookupStatement(Context $context, int $billingId, int $bundleId, int $id): LtcsBillingStatement
    {
        return $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $billingId, $bundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBillingStatement({$id}) not found");
            });
    }
}
