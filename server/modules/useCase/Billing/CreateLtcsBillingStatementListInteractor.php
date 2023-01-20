<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement as Statement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\SetupException;
use ScalikePHP\Seq;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 介護保険サービス：明細書一覧生成ユースケース実装.
 */
final class CreateLtcsBillingStatementListInteractor implements CreateLtcsBillingStatementListUseCase
{
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingStatementListInteractor} constructor.
     *
     * @param \UseCase\Billing\CreateLtcsBillingStatementUseCase $createStatementUseCase
     * @param \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase $identifyLtcsAreaGradeFeeUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \Domain\Billing\LtcsBillingStatementRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        private readonly CreateLtcsBillingStatementUseCase $createStatementUseCase,
        private readonly IdentifyLtcsAreaGradeFeeUseCase $identifyLtcsAreaGradeFeeUseCase,
        private readonly LookupUserUseCase $lookupUserUseCase,
        LtcsBillingStatementRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Office $office, LtcsBillingBundle $bundle, Seq $reports): Seq
    {
        return $this->transaction->run(function () use ($reports, $context, $office, $bundle): Seq {
            $unitCost = $this->identifyUnitCost($context, $office, $bundle);
            return Seq::from(...$bundle->details)
                ->groupBy('userId')
                ->mapValues(fn (Seq $details, int $userId): array => $this->createStatements(
                    $context,
                    $bundle,
                    $this->lookupUser($context, $userId),
                    $office,
                    $unitCost,
                    $details,
                    $reports
                ))
                ->values()
                ->flatten()
                ->computed();
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
            ->handle($context, Permission::createBillings(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }

    /**
     * 単位数単価を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\Common\Decimal
     */
    private function identifyUnitCost(Context $context, Office $office, LtcsBillingBundle $bundle): Decimal
    {
        $ltcsAreaGradeId = $office->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId;
        $targetDate = $bundle->providedIn;
        return $this->identifyLtcsAreaGradeFeeUseCase
            ->handle($context, $ltcsAreaGradeId, $targetDate)
            ->map(fn (LtcsAreaGradeFee $x): Decimal => $x->fee)
            ->getOrElse(function () use ($ltcsAreaGradeId, $targetDate): void {
                $date = $targetDate->toDateString();
                throw new SetupException("LtcsAreaGradeFee({$ltcsAreaGradeId}/{$date}) not found");
            });
    }

    /**
     * 明細書を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Decimal $unitCost
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq $reports
     * @throws \Throwable
     * @return \Domain\Billing\LtcsBillingStatement[]
     */
    private function createStatements(
        Context $context,
        LtcsBillingBundle $bundle,
        User $user,
        Office $office,
        Decimal $unitCost,
        Seq $details,
        Seq $reports
    ): array {
        return $reports
            ->filter(function (LtcsProvisionReport $x) use ($bundle, $user): bool {
                return $bundle->providedIn->isSameMonth($x->providedIn) && $x->userId === $user->id;
            })
            ->map(fn (LtcsProvisionReport $report): Statement => $this->createStatementUseCase->handle(
                $context,
                $bundle,
                $user,
                $office,
                $details,
                $unitCost,
                Seq::from($report)
            ))
            ->toArray();
    }
}
