<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス：明細書編集ユースケース実装.
 */
final class EditDwsBillingStatementInteractor implements EditDwsBillingStatementUseCase
{
    use Logging;

    private EnsureDwsBillingBundleUseCase $ensureBillingBundleUseCase;
    private LookupDwsBillingStatementUseCase $lookupUseCase;
    private DwsBillingStatementRepository $repository;
    private TransactionManager $transaction;

    public function __construct(
        EnsureDwsBillingBundleUseCase $ensureBillingBundleUseCase,
        LookupDwsBillingStatementUseCase $lookupUseCase,
        DwsBillingStatementRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureBillingBundleUseCase = $ensureBillingBundleUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        array $values
    ): DwsBillingStatement {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $dwsBillingId, $dwsBillingBundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingStatement({$id}) not found.");
            });

        /** @var \Domain\Billing\DwsBillingStatement $x */
        $x = $this->transaction->run(fn (): DwsBillingStatement => $this->repository->store(
            $entity->copy(
                $values + [
                    'updatedAt' => Carbon::now(),
                ]
            )
        ));
        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
