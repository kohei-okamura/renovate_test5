<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス：明細書状態更新ユースケース実装.
 */
final class EditDwsBillingStatementStatusInteractor implements EditDwsBillingStatementStatusUseCase
{
    use Logging;

    private ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase;
    private DwsBillingStatementRepository $repository;
    private EnsureDwsBillingBundleUseCase $ensureUseCase;
    private GetDwsBillingStatementInfoUseCase $getInfoUseCase;
    private LookupDwsBillingStatementUseCase $lookupUseCase;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase
     * @param \Domain\Billing\DwsBillingStatementRepository $repository
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureUseCase
     * @param \UseCase\Billing\GetDwsBillingStatementInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupUseCase
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase,
        DwsBillingStatementRepository $repository,
        EnsureDwsBillingBundleUseCase $ensureUseCase,
        GetDwsBillingStatementInfoUseCase $getInfoUseCase,
        LookupDwsBillingStatementUseCase $lookupUseCase,
        TransactionManagerFactory $factory
    ) {
        $this->confirmBillingStatusUseCase = $confirmBillingStatusUseCase;
        $this->repository = $repository;
        $this->ensureUseCase = $ensureUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, int $billingId, int $bundleId, int $id, DwsBillingStatus $status): array
    {
        /** @var \Domain\Billing\DwsBillingStatement $entity */
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $billingId, $bundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingStatement({$id}) not found.");
            });

        $updateEntity = $entity->copy([
            'status' => $status,
            'updatedAt' => Carbon::now(),
        ]);

        $x = $this->transaction->run(fn (): DwsBillingStatement => $this->repository->store($updateEntity));

        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        $info = $this->getInfoUseCase->handle($context, $billingId, $bundleId, $id);

        $this->confirmBillingStatusUseCase
            ->handle($context, $info['billing']);

        return $info;
    }
}
