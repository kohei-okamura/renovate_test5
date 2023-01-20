<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement as Statement;
use Domain\Billing\LtcsBillingStatementAggregate as StatementAggregate;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;
use UseCase\User\IdentifyUserLtcsSubsidyUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 介護保険サービス：明細書更新ユースケース実装.
 */
final class UpdateLtcsBillingStatementInteractor implements UpdateLtcsBillingStatementUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingStatementInteractor} constructor.
     *
     * @param \UseCase\Billing\GetLtcsBillingStatementInfoUseCase $getInfoUseCase
     * @param \UseCase\User\IdentifyUserLtcsSubsidyUseCase $identifyUserLtcsSubsidyUseCase
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupBillingUseCase
     * @param \UseCase\Billing\LookupLtcsBillingBundleUseCase $lookupBundleUseCase
     * @param \UseCase\Billing\LookupLtcsBillingStatementUseCase $lookupStatementUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase $updateInvoiceListUseCase
     * @param \Domain\Billing\LtcsBillingStatementRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        private readonly GetLtcsBillingStatementInfoUseCase $getInfoUseCase,
        private readonly IdentifyUserLtcsSubsidyUseCase $identifyUserLtcsSubsidyUseCase,
        private readonly LookupLtcsBillingUseCase $lookupBillingUseCase,
        private readonly LookupLtcsBillingBundleUseCase $lookupBundleUseCase,
        private readonly LookupLtcsBillingStatementUseCase $lookupStatementUseCase,
        private readonly LookupUserUseCase $lookupUserUseCase,
        private readonly UpdateLtcsBillingInvoiceListUseCase $updateInvoiceListUseCase,
        private readonly LtcsBillingStatementRepository $repository,
        private readonly TransactionManagerFactory $factory
    ) {
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, int $bundleId, int $id, array $values): array
    {
        $info = $this->transaction->run(fn (): array => $this->update($context, $billingId, $bundleId, $id, $values));
        $this->logger()->info(
            '介護保険サービス：明細書が更新されました',
            compact('id') + $context->logContext()
        );
        return $info;
    }

    /**
     * 明細書を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @param array $values
     * @throws \Throwable
     * @return array
     */
    private function update(Context $context, int $billingId, int $bundleId, int $id, array $values): array
    {
        $billing = $this->lookupBilling($context, $billingId);
        $bundle = $this->lookupBundle($context, $billing, $bundleId);
        $statement = $this->lookupStatement($context, $billing, $bundle, $id);

        $user = $this->lookupUser($context, $statement->user->userId);
        $userSubsidies = $this->identifyUserLtcsSubsidyUseCase->handle($context, $user, $bundle->providedIn);

        $aggregates = $this->buildAggregates($statement, $userSubsidies, $values);
        $newStatement = $statement->copy([
            'aggregates' => $aggregates,
            'updatedAt' => Carbon::now(),
        ]);
        $this->repository->store($newStatement);

        // 新しい明細書の情報を用いて請求書を更新（再生成）する.
        $this->updateInvoiceListUseCase->handle($context, $bundle);

        return $this->getInfoUseCase->handle($context, $billingId, $bundleId, $id);
    }

    /**
     * 請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Billing\LtcsBilling
     */
    private function lookupBilling(Context $context, int $id): LtcsBilling
    {
        return $this->lookupBillingUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBilling({$id}) not found.");
            });
    }

    /**
     * 請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param int $id
     * @return \Domain\Billing\LtcsBillingBundle
     */
    private function lookupBundle(Context $context, LtcsBilling $billing, int $id): LtcsBillingBundle
    {
        return $this->lookupBundleUseCase
            ->handle($context, Permission::updateBillings(), $billing, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBillingBundle({$id}) not found.");
            });
    }

    /**
     * 明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param int $id
     * @return \Domain\Billing\LtcsBillingStatement
     */
    private function lookupStatement(
        Context $context,
        LtcsBilling $billing,
        LtcsBillingBundle $bundle,
        int $id
    ): Statement {
        return $this->lookupStatementUseCase
            ->handle($context, Permission::updateBillings(), $billing, $bundle, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBillingStatement({$id}) not found.");
            });
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateBillings(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }

    /**
     * 新しい介護保険サービス請求：明細書：集計情報を組み立てる.
     *
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @param \ScalikePHP\Seq $userSubsidies
     * @param array $values
     * @return array
     */
    private function buildAggregates(Statement $statement, Seq $userSubsidies, array $values): array
    {
        $map = Seq::fromArray($values)->toMap(fn (array $x): string => $x['serviceDivisionCode']->value());
        return Seq::fromArray($statement->aggregates)
            ->map(fn (StatementAggregate $x): StatementAggregate => StatementAggregate::from(
                userSubsidies: $userSubsidies,
                benefitRate: $statement->insurance->benefitRate,
                serviceDivisionCode: $x->serviceDivisionCode,
                serviceDays: $x->serviceDays,
                plannedScore: $map
                    ->get($x->serviceDivisionCode->value())
                    ->map(fn (array $xs): int => $xs['plannedScore'])
                    ->getOrElseValue($x->plannedScore),
                managedScore: $x->managedScore,
                unmanagedScore: $x->unmanagedScore,
                unitCost: $x->insurance->unitCost
            ))
            ->toArray();
    }
}
