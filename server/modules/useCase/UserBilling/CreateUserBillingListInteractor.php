<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleFinder;
use Domain\Billing\DwsBillingFinder;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleFinder;
use Domain\Billing\LtcsBillingFinder;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportFinder;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Domain\UserBilling\UserBillingRepository;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 利用者請求一覧生成ユースケース実装
 */
class CreateUserBillingListInteractor implements CreateUserBillingListUseCase
{
    use Logging;

    private DwsBillingFinder $dwsBillingFinder;
    private LtcsBillingFinder $ltcsBillingFinder;
    private LookupOfficeUseCase $lookupOfficeUseCase;
    private LookupUserUseCase $lookupUserUseCase;
    private DwsBillingStatementFinder $dwsBillingStatementFinder;
    private LtcsBillingStatementFinder $ltcsBillingStatementFinder;
    private DwsProvisionReportFinder $dwsProvisionReportFinder;
    private LtcsProvisionReportFinder $ltcsProvisionReportFinder;
    private CreateUserBillingUseCase $createUserBillingUseCase;
    private TransactionManager $transaction;
    private DwsBillingBundleFinder $dwsBillingBundleFinder;
    private LtcsBillingBundleFinder $ltcsBillingBundleFinder;

    /**
     * constructor.
     *
     * @param \Domain\Billing\DwsBillingFinder $dwsBillingFinder
     * @param \Domain\Billing\LtcsBillingFinder $ltcsBillingFinder
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \Domain\Billing\DwsBillingStatementFinder $dwsBillingStatementFinder
     * @param \Domain\Billing\LtcsBillingStatementFinder $ltcsBillingStatementFinder
     * @param \Domain\ProvisionReport\DwsProvisionReportFinder $dwsProvisionReportFinder
     * @param \Domain\ProvisionReport\LtcsProvisionReportFinder $ltcsProvisionReportFinder
     * @param \UseCase\UserBilling\CreateUserBillingUseCase $createUserBillingUseCase
     * @param \Domain\UserBilling\UserBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     * @param \Domain\Billing\DwsBillingBundleFinder $dwsBillingBundleFinder
     * @param \Domain\Billing\LtcsBillingBundleFinder $ltcsBillingBundleFinder
     */
    public function __construct(
        DwsBillingFinder $dwsBillingFinder,
        LtcsBillingFinder $ltcsBillingFinder,
        LookupOfficeUseCase $lookupOfficeUseCase,
        LookupUserUseCase $lookupUserUseCase,
        DwsBillingStatementFinder $dwsBillingStatementFinder,
        LtcsBillingStatementFinder $ltcsBillingStatementFinder,
        DwsProvisionReportFinder $dwsProvisionReportFinder,
        LtcsProvisionReportFinder $ltcsProvisionReportFinder,
        CreateUserBillingUseCase $createUserBillingUseCase,
        UserBillingRepository $repository,
        TransactionManagerFactory $factory,
        DwsBillingBundleFinder $dwsBillingBundleFinder,
        LtcsBillingBundleFinder $ltcsBillingBundleFinder
    ) {
        $this->dwsBillingFinder = $dwsBillingFinder;
        $this->ltcsBillingFinder = $ltcsBillingFinder;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
        $this->lookupUserUseCase = $lookupUserUseCase;
        $this->dwsBillingStatementFinder = $dwsBillingStatementFinder;
        $this->ltcsBillingStatementFinder = $ltcsBillingStatementFinder;
        $this->dwsProvisionReportFinder = $dwsProvisionReportFinder;
        $this->ltcsProvisionReportFinder = $ltcsProvisionReportFinder;
        $this->createUserBillingUseCase = $createUserBillingUseCase;
        $this->transaction = $factory->factory($repository);
        $this->dwsBillingBundleFinder = $dwsBillingBundleFinder;
        $this->ltcsBillingBundleFinder = $ltcsBillingBundleFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Carbon $providedIn): void
    {
        $this->transaction->run(function () use ($context, $providedIn): void {
            $this->create($context, $providedIn);
        });
    }

    /**
     * 利用者請求一覧を生成する
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @throws \Throwable
     * @return void
     */
    private function create(Context $context, Carbon $providedIn): void
    {
        $transactedIn = $providedIn->addMonth()->firstOfMonth();
        // addMonth が CarbonImmutable を返すことにより発生する下記警告の抑止
        // Expected parameter of type '\Domain\Common\Carbon', '\Carbon\CarbonImmutable' provided
        assert($transactedIn instanceof Carbon);

        $dwsBillings = $this->findDwsBilling($context, $transactedIn);
        $dwsBundles = $this->findDwsBillingBundle($context, $providedIn, $dwsBillings);
        $dwsBillingIds = $dwsBundles->map(fn (DwsBillingBundle $x): int => $x->dwsBillingId)->distinct();
        $dwsBundleIds = $dwsBundles->map(fn (DwsBillingBundle $x): int => $x->id)->distinct();
        // 請求単位が一つも存在しない請求は除外する.
        $dwsBillingMap = $dwsBillings
            ->filter(fn (DwsBilling $x): bool => $dwsBillingIds->exists(fn (int $xx): bool => $xx === $x->id))
            ->toMap('id');
        $dwsBillingStatementMap = $this->findDwsBillingStatement($dwsBundleIds);

        $ltcsBillings = $this->findLtcsBilling($context, $transactedIn);
        $ltcsBundles = $this->findLtcsBillingBundle($context, $providedIn, $ltcsBillings);
        $ltcsBillingIds = $ltcsBundles->map(fn (LtcsBillingBundle $x): int => $x->billingId)->distinct();
        $ltcsBundleIds = $ltcsBundles->map(fn (LtcsBillingBundle $x): int => $x->id)->distinct();
        // 請求単位が一つも存在しない請求は除外する.
        $ltcsBillingMap = $ltcsBillings
            ->filter(fn (LtcsBilling $x): bool => $ltcsBillingIds->exists(fn (int $xx): bool => $xx === $x->id))
            ->toMap('id');
        $ltcsBillingStatementMap = $this->findLtcsBillingStatement($ltcsBundleIds);

        $dwsProvisionReportMap = $this->findDwsProvisionReports($providedIn, $context->organization->id)
            ->filter(fn (DwsProvisionReport $report): bool => count($report->results) > 0)
            ->groupBy('userId');

        $ltcsProvisionReportMap = $this->findLtcsProvisionReports($providedIn, $context->organization->id)
            ->filter(fn (LtcsProvisionReport $report): bool => Seq::from(...$report->entries)
            ->flatMap(fn (LtcsProvisionReportEntry $x): iterable => $x->results)
            ->nonEmpty())
            ->groupBy('userId');

        $userIds = Seq::from(...$dwsProvisionReportMap->keys(), ...$ltcsProvisionReportMap->keys())->distinct();

        foreach ($userIds as $userId) {
            $this->logger()->info(
                '利用者請求生成開始',
                ['userId' => $userId] + $context->logContext()
            );
            $dwsProvisionReports = $dwsProvisionReportMap->get($userId)->toSeq()->flatten();
            $ltcsProvisionReports = $ltcsProvisionReportMap->get($userId)->toSeq()->flatten();

            $dwsStatements = $dwsBillingStatementMap->get($userId)->toSeq()->flatten();
            $ltcsStatements = $ltcsBillingStatementMap->get($userId)->toSeq()->flatten();

            $dwsBillings = $dwsStatements
                ->flatMap(fn (DwsBillingStatement $x): iterable => $dwsBillingMap->get($x->dwsBillingId));
            $ltcsBillings = $ltcsStatements
                ->flatMap(fn (LtcsBillingStatement $x): iterable => $ltcsBillingMap->get($x->billingId));
            $officeIds = Seq::from(
                ...$dwsProvisionReports->map(fn (DwsProvisionReport $x): int => $x->officeId),
                ...$ltcsProvisionReports->map(fn (LtcsProvisionReport $x): int => $x->officeId),
            );
            foreach ($officeIds->distinct() as $officeId) {
                $dwsBilling = $dwsBillings->find(fn (DwsBilling $x): bool => $x->office->officeId === $officeId);
                $ltcsBilling = $ltcsBillings->find(fn (LtcsBilling $x): bool => $x->office->officeId === $officeId);

                $dwsBillingStatement = $dwsBilling->flatMap(
                    fn (DwsBilling $billing): Option => $dwsStatements->find(
                        fn (DwsBillingStatement $x): bool => $x->dwsBillingId === $billing->id && $x->user->userId === $userId
                    )
                );
                $ltcsBillingStatement = $ltcsBilling->flatMap(
                    fn (LtcsBilling $billing): Option => $ltcsStatements->find(
                        fn (LtcsBillingStatement $x): bool => $x->billingId === $billing->id && $x->user->userId === $userId
                    )
                );
                $dwsProvisionReport = $dwsProvisionReports->find(
                    fn (DwsProvisionReport $x): bool => $x->officeId === $officeId
                );
                $ltcsProvisionReport = $ltcsProvisionReports->find(
                    fn (LtcsProvisionReport $x): bool => $x->officeId === $officeId
                );

                try {
                    $dwsProvisionReportId = $dwsProvisionReport->isEmpty() ? '' : $dwsProvisionReport->get()->id;
                    $this->logger()->info(
                        '利用者請求生成：障害福祉サービス予実',
                        ['id' => $dwsProvisionReportId] + $context->logContext()
                    );
                    $ltcsProvisionReportId = $ltcsProvisionReport->isEmpty() ? '' : $ltcsProvisionReport->get()->id;
                    $this->logger()->info(
                        '利用者請求生成：介護保険サービス予実',
                        ['id' => $ltcsProvisionReportId] + $context->logContext()
                    );
                    $dwsBillingStatementId = $dwsBillingStatement->isEmpty() ? '' : $dwsBillingStatement->get()->id;
                    $this->logger()->info(
                        '利用者請求生成：障害福祉サービス明細書',
                        ['id' => $dwsBillingStatementId] + $context->logContext()
                    );
                    $ltcsBillingStatementId = $ltcsBillingStatement->isEmpty() ? '' : $ltcsBillingStatement->get()->id;
                    $this->logger()->info(
                        '利用者請求生成：介護保険サービス明細書',
                        ['id' => $ltcsBillingStatementId] + $context->logContext()
                    );
                    $this->createUserBillingUseCase->handle(
                        $context,
                        $this->lookupUser($context, $userId),
                        $this->lookupOffice($context, $officeId),
                        $providedIn,
                        $dwsBillingStatement,
                        $ltcsBillingStatement,
                        $dwsProvisionReport,
                        $ltcsProvisionReport
                    );
                } catch (LogicException $e) {
                    // 単一の利用者請求の作成に失敗しても処理は止めずに残りを作成する
                    $this->logger()->error($e);
                }
            }
            $this->logger()->info(
                '利用者請求生成終了',
                ['userId' => $userId] + $context->logContext()
            );
        }
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $id): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::createUserBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("user with id {$id} not found");
            });
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Office\Office
     */
    private function lookupOffice(Context $context, int $id): Office
    {
        return $this->lookupOfficeUseCase
            ->handle($context, [Permission::createUserBillings()], $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("office with id {$id} not found");
            });
    }

    /**
     * 障害福祉サービス請求を取得する.
     *
     * サービス提供年月の翌月が処理対象年月の請求を対象とする.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $transactedIn
     * @return DwsBilling[]|\ScalikePHP\Seq
     */
    private function findDwsBilling(Context $context, Carbon $transactedIn): Seq
    {
        $filterParams = [
            'organizationId' => $context->organization->id,
            'transactedIn' => $transactedIn,
            'status' => DwsBillingStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
            'desc' => true,
        ];
        return $this->dwsBillingFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->distinctBy(fn (DwsBilling $x) => $x->office->officeId);
    }

    /**
     * 介護保険サービス請求を取得する.
     *
     * サービス提供年月の翌月が処理対象年月の請求を対象とする.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $transactedIn
     * @return LtcsBilling[]|\ScalikePHP\Seq
     */
    private function findLtcsBilling(Context $context, Carbon $transactedIn): Seq
    {
        $filterParams = [
            'organizationId' => $context->organization->id,
            'transactedIn' => $transactedIn,
            'status' => LtcsBillingStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
            'desc' => true,
        ];
        return $this->ltcsBillingFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->distinctBy(fn (LtcsBilling $x) => $x->office->officeId);
    }

    /**
     * 障害福祉サービス請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBilling[]|\ScalikePHP\Seq $dwsBillings
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    private function findDwsBillingBundle(Context $context, Carbon $providedIn, Seq $dwsBillings): Seq
    {
        $filterParams = [
            'organizationId' => $context->organization->id,
            'providedIn' => $providedIn,
            'dwsBillingIds' => $dwsBillings->map(fn (DwsBilling $x): int => $x->id)->distinct()->toArray(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dwsBillingBundleFinder
            ->find($filterParams, $paginationParams)
            ->list;
    }

    /**
     * 介護保険サービス請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\LtcsBilling[]|\ScalikePHP\Seq $ltcsBillings
     * @return \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq
     */
    private function findLtcsBillingBundle(Context $context, Carbon $providedIn, Seq $ltcsBillings): Seq
    {
        $filterParams = [
            'organizationId' => $context->organization->id,
            'providedIn' => $providedIn,
            'billingIds' => $ltcsBillings->map(fn (LtcsBilling $x) => $x->id)->distinct()->toArray(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->ltcsBillingBundleFinder
            ->find($filterParams, $paginationParams)
            ->list;
    }

    /**
     * 障害福祉サービス請求明細書を取得する.
     *
     * @param int[]|\ScalikePHP\Seq $bundleIds
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Map
     */
    private function findDwsBillingStatement(Seq $bundleIds): Map
    {
        $filterParams = [
            'dwsBillingBundleIds' => $bundleIds->toArray(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dwsBillingStatementFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->groupBy(fn (DwsBillingStatement $x): int => $x->user->userId);
    }

    /**
     * 介護保険サービス請求明細書を取得する.
     *
     * @param int[]|\ScalikePHP\Seq $bundleIds
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Map
     */
    private function findLtcsBillingStatement(Seq $bundleIds): Map
    {
        $filterParams = [
            'bundleIds' => $bundleIds->toArray(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->ltcsBillingStatementFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->groupBy(fn (LtcsBillingStatement $x): int => $x->user->userId);
    }

    /**
     * 障害福祉サービス予実を取得する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param int $organizationId
     * @return \Domain\Entity[]|\ScalikePHP\Seq
     */
    private function findDwsProvisionReports(Carbon $providedIn, int $organizationId): Seq
    {
        $filterParams = [
            'providedIn' => $providedIn,
            'status' => DwsProvisionReportStatus::fixed(),
            'organizationId' => $organizationId,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dwsProvisionReportFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 介護保険サービス予実を取得する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param int $organizationId
     * @return \Domain\Entity[]|\ScalikePHP\Seq
     */
    private function findLtcsProvisionReports(Carbon $providedIn, int $organizationId): Seq
    {
        $filterParams = [
            'providedIn' => $providedIn,
            'status' => LtcsProvisionReportStatus::fixed(),
            'organizationId' => $organizationId,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->ltcsProvisionReportFinder->find($filterParams, $paginationParams)->list;
    }
}
