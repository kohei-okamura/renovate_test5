<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement as Statement;
use Domain\Billing\DwsBillingStatementAggregate as Aggregate;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス 明細書更新ユースケース 実装.
 */
class UpdateDwsBillingStatementInteractor implements UpdateDwsBillingStatementUseCase
{
    use Logging;

    private BuildDwsBillingStatementForUpdateUseCase $buildUseCase;
    private DwsBillingStatementRepository $repository;
    private EnsureDwsBillingBundleUseCase $ensureUseCase;
    private GetDwsBillingStatementInfoUseCase $getInfoUseCase;
    private LookupDwsBillingStatementUseCase $lookupUseCase;
    private TransactionManager $transaction;
    private UpdateDwsBillingInvoiceUseCase $updateInvoiceUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase $buildUseCase
     * @param \Domain\Billing\DwsBillingStatementRepository $repository
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureUseCase
     * @param \UseCase\Billing\GetDwsBillingStatementInfoUseCase $getInfoUseCase
     * @param \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupUseCase
     * @param \Domain\TransactionManagerFactory $factory
     * @param \UseCase\Billing\UpdateDwsBillingInvoiceUseCase $updateInvoiceUseCase
     */
    public function __construct(
        BuildDwsBillingStatementForUpdateUseCase $buildUseCase,
        DwsBillingStatementRepository $repository,
        EnsureDwsBillingBundleUseCase $ensureUseCase,
        GetDwsBillingStatementInfoUseCase $getInfoUseCase,
        LookupDwsBillingStatementUseCase $lookupUseCase,
        TransactionManagerFactory $factory,
        UpdateDwsBillingInvoiceUseCase $updateInvoiceUseCase
    ) {
        $this->repository = $repository;
        $this->ensureUseCase = $ensureUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->buildUseCase = $buildUseCase;
        $this->transaction = $factory->factory($repository);
        $this->updateInvoiceUseCase = $updateInvoiceUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, int $bundleId, int $id, array $values): array
    {
        /** @var \Domain\Billing\DwsBillingStatement $entity */
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $billingId, $bundleId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBillingStatement({$id}) not found.");
            });

        $aggregates = call_user_func(function () use ($entity, $values): iterable {
            $aggregates = Seq::fromArray($entity->aggregates);
            foreach ($values as $updateValue) {
                assert($updateValue['serviceDivisionCode'] instanceof DwsServiceDivisionCode);
                $aggregate = $aggregates
                    ->find(fn (Aggregate $x): bool => $x->serviceDivisionCode === $updateValue['serviceDivisionCode'])
                    ->getOrElse(function () use ($updateValue): void {
                        throw new LogicException("ServiceDivisionCode({$updateValue['serviceDivisionCode']}) invalid");
                    });
                yield $aggregate->copy([
                    'managedCopay' => $updateValue['managedCopay'],
                    'subtotalSubsidy' => $updateValue['subtotalSubsidy'],
                ]);
            }
        });
        $entityForUpdate = $entity->copy([
            'aggregates' => [...$aggregates],
        ]);
        $entityForStore = $this->buildUseCase->handle($context, $entityForUpdate);

        $x = $this->transaction->run(
            function () use ($entityForStore, $entityForUpdate, $context, $billingId, $bundleId): Statement {
                $statement = $this->repository->store(
                    $entityForStore->copy([
                        'id' => $entityForUpdate->id,
                        'updatedAt' => Carbon::now(),
                    ])
                );

                $this->updateInvoiceUseCase->handle($context, $billingId, $bundleId);

                return $statement;
            }
        );

        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $this->getInfoUseCase->handle($context, $billingId, $bundleId, $id);
    }
}
