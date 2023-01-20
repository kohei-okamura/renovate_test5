<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportFinder;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\SetupException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 介護保険サービス：明細書リフレッシュユースケース実装.
 */
class RefreshLtcsBillingStatementInteractor implements RefreshLtcsBillingStatementUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\RefreshLtcsBillingStatementInteractor} constructor.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportFinder $provisionReportFinder
     * @param \UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase $lookupStatementUseCase
     * @param \UseCase\Billing\LookupLtcsBillingBundleUseCase $lookupBundleUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\Billing\BuildLtcsBillingStatementUseCase $buildStatementUseCase
     * @param \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase $identifyLtcsAreaGradeFeeUseCase
     * @param \UseCase\Billing\BuildLtcsServiceDetailListUseCase $buildServiceDetailListUseCase
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupBillingUseCase
     * @param \UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase $updateInvoiceListUseCase
     * @param \Domain\Billing\LtcsBillingStatementRepository $statementRepository
     * @param \Domain\Billing\LtcsBillingBundleRepository $bundleRepository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        private readonly LtcsProvisionReportFinder $provisionReportFinder,
        private readonly SimpleLookupLtcsBillingStatementUseCase $lookupStatementUseCase,
        private readonly LookupLtcsBillingBundleUseCase $lookupBundleUseCase,
        private readonly LookupOfficeUseCase $lookupOfficeUseCase,
        private readonly LookupUserUseCase $lookupUserUseCase,
        private readonly BuildLtcsBillingStatementUseCase $buildStatementUseCase,
        private readonly IdentifyLtcsAreaGradeFeeUseCase $identifyLtcsAreaGradeFeeUseCase,
        private readonly BuildLtcsServiceDetailListUseCase $buildServiceDetailListUseCase,
        private readonly LookupLtcsBillingUseCase $lookupBillingUseCase,
        private readonly UpdateLtcsBillingInvoiceListUseCase $updateInvoiceListUseCase,
        private readonly LtcsBillingStatementRepository $statementRepository,
        private readonly LtcsBillingBundleRepository $bundleRepository,
        TransactionManagerFactory $factory
    ) {
        $this->transaction = $factory->factory($statementRepository, $bundleRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, array $statementIds): void
    {
        [$storedBundle, $storedStatements] = $this->transaction->run(fn (): array => $this->refresh(
            $context,
            $billingId,
            $statementIds
        ));
        $this->logger()->info('介護保険サービス：請求単位が更新されました', [
            ...$context->logContext(),
            'id' => $storedBundle->id,
        ]);
        $this->logger()->info('介護保険サービス：明細書が更新されました', [
            ...$context->logContext(),
            'id' => implode(',', $storedStatements->map(fn (LtcsBillingStatement $x): int => $x->id)->toArray()),
        ]);
    }

    /**
     * 介護保険サービス：明細書をリフレッシュする.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param array $ids
     * @throws \Throwable
     * @return array
     */
    private function refresh(Context $context, int $billingId, array $ids): array
    {
        $billing = $this->lookupBilling($context, $billingId);
        $statements = $this->lookupStatementUseCase->handle($context, Permission::updateBillings(), ...$ids);
        $bundle = $this->lookupBundle(
            $context,
            $billing,
            $statements->map(fn (LtcsBillingStatement $x): int => $x->bundleId)->distinct()->toArray()
        );
        $office = $this->lookupOffice($context, $billing->office->officeId);
        $fixedAt = CarbonRange::create([
            'start' => $billing->transactedIn->subMonth()->day(11),
            'end' => $billing->transactedIn->day(10)->endOfDay(),
        ]);
        $reports = $this->findProvisionReports(
            $office->id,
            $statements->map(fn (LtcsBillingStatement $x) => $x->user->userId)->toArray(),
            $bundle->providedIn,
            $fixedAt
        );
        $userIds = $reports->map(fn (LtcsProvisionReport $x): int => $x->userId);
        $users = $this->lookupUsers($context, $userIds);
        $newDetails = $this->buildDetails(
            $context,
            $reports,
            $bundle->providedIn,
            $users,
            $bundle->details,
        );
        $newBundle = $bundle->copy([
            'details' => $newDetails->toArray(),
            'updatedAt' => Carbon::now(),
        ]);
        $newStatements = $this->buildStatements($context, $newBundle, $office, $users, $reports, $newDetails);

        $storedBundle = $this->bundleRepository->store($newBundle);
        $storedStatements = $this->storeStatements($statements, $newStatements);
        $this->updateInvoiceListUseCase->handle($context, $storedBundle);

        return [$storedBundle, $storedStatements];
    }

    /**
     * 対象となる介護保険サービス：予実の一覧をサービス提供年月ごとに取得する.
     *
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Common\CarbonRange $fixedAt
     * @param array $userIds
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq
     */
    private function findProvisionReports(int $officeId, array $userIds, Carbon $providedIn, CarbonRange $fixedAt): Seq
    {
        $filterParams = [
            'officeId' => $officeId,
            'userIds' => $userIds,
            'providedIn' => $providedIn,
            'fixedAt' => $fixedAt,
            'status' => LtcsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        $xs = $this->provisionReportFinder->find($filterParams, $paginationParams)->list;
        if ($xs->isEmpty()) {
            $start = $fixedAt->start->toDateString();
            $end = $fixedAt->end->toDateString();
            throw new NotFoundException(
                "LtcsProvisionReports that fixed at {$start}〜{$end} not found for Office({$officeId})"
            );
        }
        return $xs;
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
     * 介護保険サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param $id
     * @return \Domain\Billing\LtcsBilling
     */
    private function lookupBilling(Context $context, $id): LtcsBilling
    {
        return $this->lookupBillingUseCase->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBilling({$id}) not found");
            });
    }

    /**
     * 介護保険サービス：請求：請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param array $ids
     * @return \Domain\Billing\LtcsBillingBundle
     */
    private function lookupBundle(Context $context, LtcsBilling $billing, array $ids): LtcsBillingBundle
    {
        return $this->lookupBundleUseCase
            ->handle($context, Permission::updateBillings(), $billing, ...$ids)
            ->headOption()
            ->getOrElse(function () use ($ids): void {
                throw new NotFoundException("LtcsBillingBundle{$ids[0]} not found");
            });
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param $id
     * @return \Domain\Office\Office
     */
    private function lookupOffice(Context $context, $id): Office
    {
        return $this->lookupOfficeUseCase->handle($context, [Permission::updateBillings()], $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Office({$id}) not found");
            });
    }

    /**
     * 更新用の介護保険サービス：サービス詳細一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq $provisionReports
     * @param \Domain\Common\Carbon $providedIn
     * @param array&\Domain\Billing\LtcsBillingServiceDetail[] $details
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    private function buildDetails(
        Context $context,
        Seq $provisionReports,
        Carbon $providedIn,
        Seq $users,
        array $details
    ): Seq {
        $xs = $this->buildServiceDetailListUseCase->handle($context, $providedIn, $provisionReports, $users);
        $newDetailsMap = Seq::from(...$xs)->groupBy('userId');
        // 古いサービス詳細を利用者ごとに groupBy し, 新しいサービス詳細がある場合のみ差し替える
        return Seq::from(...$details)
            ->groupBy('userId')
            ->mapValues(fn (Seq $xs, int $userId): Seq => $newDetailsMap->getOrElse($userId, fn (): Seq => $xs))
            ->values()
            ->flatten()
            ->computed();
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[]&\ScalikePHP\Seq $userIds
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    private function lookupUsers(Context $context, Seq $userIds): Seq
    {
        $users = $this->lookupUserUseCase
            ->handle($context, Permission::updateBillings(), ...$userIds->toArray());
        if ($users->isEmpty()) {
            $x = implode(',', $userIds);
            throw new NotFoundException("User ({$x}) not found");
        }
        return $users;
    }

    /**
     * 新しい明細書の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Office\Office $office
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq $reports
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @return \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Map
     */
    private function buildStatements(
        Context $context,
        LtcsBillingBundle $bundle,
        Office $office,
        Seq $users,
        Seq $reports,
        Seq $details
    ): Map {
        $usersMap = $users->toMap('id');
        $unitCost = $this->identifyUnitCost($context, $office, $bundle);
        return $details
            ->groupBy('userId')
            ->mapValues(fn (Seq $xs, int $userId): LtcsBillingStatement => $this->buildStatement(
                context: $context,
                reports: $reports,
                bundle: $bundle,
                user: $usersMap->get($userId)->getOrElse(function () use ($userId): void {
                    throw new NotFoundException("User ({$userId}) not found");
                }),
                details: $details,
                unitCost: $unitCost,
                office: $office
            ));
    }

    /**
     * 新しい明細書を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq $reports
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param \Domain\Common\Decimal $unitCost
     * @param \Domain\Office\Office $office
     * @return \Domain\Billing\LtcsBillingStatement
     */
    private function buildStatement(
        Context $context,
        Seq $reports,
        LtcsBillingBundle $bundle,
        User $user,
        Seq $details,
        Decimal $unitCost,
        Office $office
    ): LtcsBillingStatement {
        return $this->buildStatementUseCase->handle(
            $context,
            $bundle,
            $user,
            $office,
            $details,
            $unitCost,
            $reports
        );
    }

    /**
     * 明細書をリポジトリに格納する.
     *
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $baseStatements
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Map $newStatementsMap
     * @return \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq
     */
    private function storeStatements(Seq $baseStatements, Map $newStatementsMap): Seq
    {
        // 遅延すると後続の請求書生成時に明細書が保管されておらず正しく請求書が生成できなくなるためここで計算を行う,
        return $baseStatements
            ->map(function (LtcsBillingStatement $x) use ($newStatementsMap): LtcsBillingStatement {
                /** @var \Domain\Billing\LtcsBillingStatement $newStatement */
                $newStatement = $newStatementsMap->getOrElse($x->user->userId, function () use ($x): void {
                    throw new NotFoundException("LtcsBillingStatement(userId={$x->user->userId}) not found");
                });
                return $this->statementRepository->store($x->copy([
                    'insurerNumber' => $newStatement->insurerNumber,
                    'insurerName' => $newStatement->insurerName,
                    'user' => $newStatement->user,
                    'carePlanAuthor' => $newStatement->carePlanAuthor,
                    'agreedOn' => $newStatement->agreedOn,
                    'expiredOn' => $newStatement->expiredOn,
                    'expiredReason' => $newStatement->expiredReason,
                    'insurance' => $newStatement->insurance,
                    'subsidies' => $newStatement->subsidies,
                    'items' => $newStatement->items,
                    'aggregates' => $newStatement->aggregates,
                    'status' => LtcsBillingStatus::ready(),
                    'fixedAt' => $newStatement->fixedAt,
                    'updatedAt' => Carbon::now(),
                ]));
            })
            ->computed();
    }
}
