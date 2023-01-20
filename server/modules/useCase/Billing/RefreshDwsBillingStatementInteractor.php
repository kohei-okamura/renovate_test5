<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleFinder;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceFinder;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportFinder;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\Office;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 障害福祉サービス：明細書等リフレッシュユースケース実装.
 */
class RefreshDwsBillingStatementInteractor implements RefreshDwsBillingStatementUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementUseCase $buildDwsBillingStatementUseCase
     * @param \UseCase\Billing\BuildDwsBillingSourceListUseCase $buildDwsBillingSourceListUseCase
     * @param \UseCase\Billing\BuildDwsBillingServiceDetailListUseCase $buildBillingServiceDetailListUseCase
     * @param \UseCase\Billing\CreateDwsBillingInvoiceUseCase $createDwsBillingInvoiceUseCase
     * @param \Domain\Billing\DwsBillingBundleFinder $bundleFinder
     * @param \Domain\Billing\DwsBillingBundleRepository $bundleRepository
     * @param \Domain\Billing\DwsBillingCopayCoordinationFinder $dwsBillingCopayCoordinationFinder
     * @param \Domain\Billing\DwsBillingInvoiceFinder $billingInvoiceFinder
     * @param \Domain\Billing\DwsBillingInvoiceRepository $invoiceRepository
     * @param \Domain\Billing\DwsBillingServiceReportFinder $dwsBillingServiceReportFinder
     * @param \Domain\Billing\DwsBillingStatementRepository $statementRepository
     * @param \Domain\ProvisionReport\DwsProvisionReportFinder $dwsProvisionReportFinder
     * @param \UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase
     * @param \UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupDwsBillingUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\Billing\UpdateDwsBillingInvoiceUseCase $updateDwsBillingInvoiceUseCase
     * @param \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase
     * @param \UseCase\Billing\RefreshDwsBillingServiceReportUseCase $refreshDwsBillingServiceReportUseCase
     * @param \UseCase\Billing\RefreshDwsBillingCopayCoordinationUseCase $refreshDwsBillingCopayCoordinationUseCase
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        private BuildDwsBillingStatementUseCase $buildDwsBillingStatementUseCase,
        private BuildDwsBillingSourceListUseCase $buildDwsBillingSourceListUseCase,
        private BuildDwsBillingServiceDetailListUseCase $buildBillingServiceDetailListUseCase,
        private CreateDwsBillingInvoiceUseCase $createDwsBillingInvoiceUseCase,
        private DwsBillingBundleFinder $bundleFinder,
        private DwsBillingBundleRepository $bundleRepository,
        private DwsBillingCopayCoordinationFinder $dwsBillingCopayCoordinationFinder,
        private DwsBillingInvoiceFinder $billingInvoiceFinder,
        private DwsBillingInvoiceRepository $invoiceRepository,
        private DwsBillingServiceReportFinder $dwsBillingServiceReportFinder,
        private DwsBillingStatementRepository $statementRepository,
        private DwsProvisionReportFinder $dwsProvisionReportFinder,
        private IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase,
        private IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase,
        private IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
        private LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        private LookupOfficeUseCase $lookupOfficeUseCase,
        private LookupUserUseCase $lookupUserUseCase,
        private UpdateDwsBillingInvoiceUseCase $updateDwsBillingInvoiceUseCase,
        private SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase,
        private RefreshDwsBillingServiceReportUseCase $refreshDwsBillingServiceReportUseCase,
        private RefreshDwsBillingCopayCoordinationUseCase $refreshDwsBillingCopayCoordinationUseCase,
        TransactionManagerFactory $factory
    ) {
        $this->transaction = $factory->factory($statementRepository, $bundleRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, array $ids): void
    {
        $statements = $this->lookupDwsBillingStatements($context, ...$ids);
        $statementsByUser = $statements->toMap(fn (DwsBillingStatement $x): int => $x->user->userId);

        $userIds = $statements->map(fn (DwsBillingStatement $x): int => $x->user->userId)->toArray();
        $users = $this->lookupUsers($context, ...$userIds);

        $bundleIds = $statements->map(fn (DwsBillingStatement $x): int => $x->dwsBillingBundleId)->distinct()->toArray();

        // 請求に紐づく全ての請求単位
        $allBundles = $this->findBundles($billingId);

        $providedIn = $this->resolveProvidedIn($allBundles, $bundleIds);

        $bundlesInTargetMonth = $allBundles->filter(fn (DwsBillingBundle $x): bool => $x->providedIn->isSameMonth($providedIn));

        $billing = $this->lookupDwsBilling($context, $billingId);
        $office = $this->lookupOffice($context, $billing->office->officeId);
        // リフレッシュに使用する予実を取得する
        $provisionReports = $this->findProvisionReports($office, $userIds, $providedIn);
        $previousProvisionReports = $this->findPreviousProvisionReports($office, $userIds, $providedIn);
        $provisionReportsByUser = $provisionReports->groupBy(fn (DwsProvisionReport $x): int => $x->userId);
        $previousProvisionReportsByUser = $previousProvisionReports->groupBy(fn (DwsProvisionReport $x): int => $x->userId);
        // 市町村コードがキーのマップをつくる
        $serviceDetailSetsByCityCode = $this->buildServiceDetail($context, $provisionReports, $office, $providedIn, $previousProvisionReports);

        $usersById = $users->toMap('id');
        $homeHelpServiceCalcSpec = $this->identifyHomeHelpServiceCalcSpec($context, $office, $providedIn);
        $visitingCareForPwsdCalcSpec = $this->identifyVisitingCareForPwsdCalcSpec($context, $office, $providedIn);
        $serviceReports = $this->findServiceReport($bundleIds, $userIds);
        $serviceReportsByUser = $serviceReports->groupBy(fn (DwsBillingServiceReport $x): int => $x->user->userId);
        $copayCoordinations = $this->findDwsBillingCopayCoordination($bundleIds, $userIds);
        $copayCoordinationsByUser = $copayCoordinations->toMap(fn (DwsBillingCopayCoordination $x): int => $x->user->userId);

        /** @var \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $storedInvoices */
        /** @var \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $disusedInvoices */
        /** @var \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $updatedInvoices */
        [$storedInvoices, $disusedInvoices, $updatedInvoices] = $this->transaction->run(function () use (
            $bundlesInTargetMonth,
            $userIds,
            $usersById,
            $visitingCareForPwsdCalcSpec,
            $homeHelpServiceCalcSpec,
            $office,
            $context,
            $serviceReportsByUser,
            $provisionReportsByUser,
            $previousProvisionReportsByUser,
            $statementsByUser,
            $copayCoordinationsByUser,
            $providedIn,
            $billingId,
            $serviceDetailSetsByCityCode
        ): array {
            // 最初に更新対象の利用者に対応するサービス詳細を削除しておく
            $bundlesRemovedTargetDetails = $this->removeDetailsForUsers($bundlesInTargetMonth, $userIds);

            $bundlesByCityCode = $bundlesRemovedTargetDetails->toMap('cityCode');

            // 請求書の更新が必要かどうか判定用
            // この時点では更新対象の利用者に対応するサービス詳細は空
            // 更新した請求単位に置き換えていき、最終的に請求単位のサービス詳細が存在するかで判定する
            $bundlesByCityCodeAssoc = $bundlesByCityCode->toAssoc();

            // 最後にログにidを出すために更新した請求書を保存しておく
            $storedInvoices = [];

            // $serviceDetailsから同一の市町村コードのbundleのdetailsに足す。
            foreach ($serviceDetailSetsByCityCode as $cityCode => $detailSet) {
                // 市町村コードに対応したbundleを取り出す、なければ生成する
                $bundle = $bundlesByCityCode->getOrElse($cityCode, fn (): DwsBillingBundle => DwsBillingBundle::create([
                    'dwsBillingId' => $billingId,
                    'providedIn' => $providedIn,
                    'cityCode' => $cityCode,
                    'cityName' => $detailSet['cityName'],
                    // repositoryに保存する直前に追加しているのでここでは空にしておく.
                    'details' => [],
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ]));
                $isNewBundle = empty($bundle->id);

                // 新しい請求単位の場合は id を得るために一旦保管する.
                $savedBundle = $isNewBundle
                    ? $this->bundleRepository->store($bundle)
                    : $bundle;
                // 請求書を生成するためにbundleごとに明細書を保存しておく必要がある
                // 色々検討したが可読性を考慮しイミュータブルは諦めることとなった。
                $storedStatements = [];
                // サービス詳細の利用者ごとにリフレッシュして保存
                foreach (Seq::fromArray($detailSet['details'])->groupBy('userId') as $userId => $details) {
                    $copayCoordination = $copayCoordinationsByUser->get($userId);
                    /** @var \Domain\Billing\DwsBillingStatement $statement */
                    $statement = $statementsByUser->getOrElse($userId, function () {
                        // ここ到達する前に別の例外になるので通ることはない.
                        throw new LogicException('statement of userId does not exist.'); // @codeCoverageIgnore
                    });
                    $provisionReports = $provisionReportsByUser->getOrElse($userId, fn (): Seq => Seq::empty());
                    $previousProvisionReports = $previousProvisionReportsByUser->getOrElse($userId, fn (): Seq => Seq::empty());
                    $serviceReports = $serviceReportsByUser->getOrElse($userId, fn (): Seq => Seq::empty());
                    $user = $usersById->getOrElse($userId, function () {
                        // ここ到達する前に別の例外になるので通ることはない
                        throw new LogicException('user of userId does not exist.'); // @codeCoverageIgnore
                    });

                    $dwsCertification = $this->identifyDwsCertification(
                        $context,
                        $userId,
                        $providedIn
                    );

                    // 上限管理区分・結果は変更したいので更新前の明細書は渡さない。
                    // 生成時と同様に上限額管理結果票は Option::none を常に渡す
                    $updateStatement = $this->buildDwsBillingStatementUseCase->handle(
                        $context,
                        $office,
                        $savedBundle,
                        $homeHelpServiceCalcSpec,
                        $visitingCareForPwsdCalcSpec,
                        $user,
                        $details,
                        Option::none(),
                        Option::none()
                    );

                    // サービス提供実績記録票をリフレッシュ
                    $this->refreshDwsBillingServiceReportUseCase->handle(
                        $context,
                        $savedBundle,
                        $provisionReports,
                        $serviceReports,
                        $previousProvisionReports
                    );

                    // 上限管理結果票をリフレッシュ
                    $this->refreshDwsBillingCopayCoordinationUseCase->handle(
                        $context,
                        $updateStatement,
                        $copayCoordination,
                        $dwsCertification,
                        $office
                    );

                    $copayCoordinationStatus = $this->buildUpdateCopayCoordinationStatus(
                        $dwsCertification,
                        $office,
                        $copayCoordination,
                        $updateStatement
                    );

                    $storedStatements[] = $this->statementRepository->store($updateStatement->copy([
                        'id' => $statement->id,
                        'status' => $statement->status === DwsBillingStatus::checking()
                            ? DwsBillingStatus::checking()
                            : DwsBillingStatus::ready(),
                        'copayCoordinationStatus' => $copayCoordinationStatus,
                        'createdAt' => $statement->createdAt,
                    ]));
                }

                // 請求単位のサービス詳細をリフレッシュして保存
                $updatedBundle = $this->bundleRepository->store(
                    $savedBundle->copy([
                        'details' => [...$bundle->details, ...$detailSet['details']],
                        'updatedAt' => Carbon::now(),
                    ])
                );
                if ($isNewBundle) {
                    $storedInvoices[] = $this->createDwsBillingInvoiceUseCase->handle($context, $updatedBundle, Seq::fromArray($storedStatements));
                }
                // 請求書の更新が必要かどうか判定用
                // 更新した請求単位に置き換える
                $bundlesByCityCodeAssoc[$cityCode] = $updatedBundle;
            }

            // サービス詳細が一つもなくなった請求単位は削除する
            // サービス詳細が存在する請求単位は請求書を更新する
            [$disusedBundles, $updatedBundles] = Seq::fromArray(array_values($bundlesByCityCodeAssoc))
                ->partition(fn (DwsBillingBundle $x): bool => empty($x->details));
            // 変更があった請求単位のうち請求単位が存在する全ての請求書を生成しなおして登録する
            $updatedInvoices = $updatedBundles->map(function (DwsBillingBundle $x) use ($context) {
                return $this->updateDwsBillingInvoiceUseCase->handle(
                    $context,
                    $x->dwsBillingId,
                    $x->id
                );
            });
            // 削除する請求単位の請求書を削除する
            if ($disusedBundles->nonEmpty()) {
                $disusedBundleIds = $disusedBundles->map(fn (DwsBillingBundle $x): int => $x->id);
                $disusedInvoices = $this->findInvoices($disusedBundleIds->toArray());
                $disusedInvoices->each(fn (DwsBillingInvoice $x) => $this->invoiceRepository->remove($x));
                // 削除する請求単位の請求単位を削除する
                $disusedBundles->each(fn (DwsBillingBundle $x) => $this->bundleRepository->remove($x));
            } else {
                $disusedInvoices = Seq::empty();
            }

            return [
                Seq::fromArray($storedInvoices),
                $disusedInvoices,
                $updatedInvoices,
            ];
        });

        $this->outputLogs($context, $storedInvoices, $disusedInvoices, $updatedInvoices, $statements);
    }

    /**
     * 障害福祉サービス請求明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[]|\ScalikePHP\Seq $ids
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Map
     */
    private function lookupDwsBillingStatements(Context $context, int ...$ids): Seq
    {
        $statements = $this->lookupDwsBillingStatementUseCase->handle($context, Permission::updateBillings(), ...$ids);
        if ($statements->size() !== count($ids)) {
            $idList = implode(
                ',',
                $statements
                    ->filter(fn (DwsBillingStatement $x): bool => !in_array($x->id, $ids, true))
                    ->toArray()
            );
            throw new NotFoundException("DwsBillingStatement({$idList}) not found");
        }
        return $statements;
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @return \Domain\Office\Office
     */
    private function lookupOffice(Context $context, int $officeId): Office
    {
        return $this->lookupOfficeUseCase
            ->handle($context, [Permission::updateBillings()], $officeId)
            ->headOption()
            ->getOrElse(function () use ($officeId): void {
                throw new NotFoundException("Office({$officeId}) not found");
            });
    }

    /**
     * 障害福祉サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @return \Domain\Billing\DwsBilling
     */
    private function lookupDwsBilling(Context $context, int $billingId): DwsBilling
    {
        return $this->lookupDwsBillingUseCase
            ->handle($context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->getOrElse(function () use ($billingId): void {
                throw new NotFoundException("DwsBilling({$billingId}) not found.");
            });
    }

    /**
     * 障害福祉サービス：利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     * @return \Domain\User\User[]|\ScalikePHP\Seq
     */
    private function lookupUsers(Context $context, int ...$ids): Seq
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateBillings(), ...$ids);
    }

    /**
     * 障害福祉サービス：予実を取得する
     *
     * @param \Domain\Office\Office $office
     * @param array|int[] $userIds
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq
     */
    private function findProvisionReports(Office $office, array $userIds, Carbon $providedIn): Seq
    {
        $filterParams = [
            'officeId' => $office->id,
            'userIds' => $userIds,
            'providedIn' => $providedIn,
            'status' => DwsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        $xs = $this->dwsProvisionReportFinder->find($filterParams, $paginationParams)->list;
        if ($xs->count() !== count($userIds)) {
            throw new NotFoundException(
                "DwsProvisionReports that provided In {$providedIn} not found for Office({$office->id})"
            );
        }
        return $xs;
    }

    /**
     * 前月分の障害福祉サービス：予実を取得する
     *
     * @param \Domain\Office\Office $office
     * @param array|int[] $userIds
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq
     */
    private function findPreviousProvisionReports(Office $office, array $userIds, Carbon $providedIn): Seq
    {
        $filterParams = [
            'officeId' => $office->id,
            'userIds' => $userIds,
            'providedIn' => $providedIn->subMonth(),
            'status' => DwsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dwsProvisionReportFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 障害福祉サービス：請求単位を取得する.
     *
     * @param int $billingId
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    private function findBundles(int $billingId): Seq
    {
        $filterParams = ['dwsBillingId' => $billingId];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->bundleFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * サービス提供実績記録票を取得する.
     *
     * @param array|int[] $bundleIds
     * @param array|int[] $userIds
     * @return \Domain\Billing\DwsBillingServiceReport[]|\ScalikePHP\Seq
     */
    private function findServiceReport(array $bundleIds, array $userIds): Seq
    {
        $filterParams = [
            'dwsBillingBundleIds' => $bundleIds,
            'userIds' => $userIds,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dwsBillingServiceReportFinder
            ->find($filterParams, $paginationParams)
            ->list;
    }

    /**
     * 利用者負担上限額管理結果票を取得する.
     *
     * @param array|int[] $bundleIds
     * @param array|int[] $userIds
     * @return \Domain\Billing\DwsBillingCopayCoordination[]|\ScalikePHP\Seq
     */
    private function findDwsBillingCopayCoordination(array $bundleIds, array $userIds): Seq
    {
        $filterParams = [
            'dwsBillingBundleIds' => $bundleIds,
            'userIds' => $userIds,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dwsBillingCopayCoordinationFinder
            ->find($filterParams, $paginationParams)
            ->list;
    }

    /**
     * 障害福祉サービス：居宅介護：算定情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Office\HomeHelpServiceCalcSpec
     */
    private function identifyHomeHelpServiceCalcSpec(
        Context $context,
        Office $office,
        Carbon $providedIn
    ): ?HomeHelpServiceCalcSpec {
        return $this->identifyHomeHelpServiceCalcSpecUseCase
            ->handle($context, $office, $providedIn)
            ->orNull();
    }

    /**
     * 障害福祉サービス：重度訪問介護：算定情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec
     */
    private function identifyVisitingCareForPwsdCalcSpec(
        Context $context,
        Office $office,
        Carbon $providedIn
    ): ?VisitingCareForPwsdCalcSpec {
        return $this->identifyVisitingCareForPwsdCalcSpecUseCase
            ->handle($context, $office, $providedIn)
            ->orNull();
    }

    /**
     * 障害福祉サービス：重度訪問介護：算定情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyDwsCertification(
        Context $context,
        int $userId,
        Carbon $providedIn
    ): DwsCertification {
        return $this->identifyDwsCertificationUseCase
            ->handle($context, $userId, $providedIn)
            ->getOrElse(function () use ($userId, $providedIn): void {
                $date = $providedIn->toDateString();
                throw new NotFoundException("DwsCertification for User({$userId}) at {$date} not found");
            });
    }

    /**
     * サービス詳細を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq $provisionReports
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq $previousProvisionReports
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Map
     */
    private function buildServiceDetail(
        Context $context,
        Seq $provisionReports,
        Office $office,
        Carbon $providedIn,
        Seq $previousProvisionReports
    ): Map {
        $sources = $this->buildDwsBillingSourceListUseCase->handle(
            $context,
            $provisionReports,
            $previousProvisionReports->computed()
        );
        return $this->buildBillingServiceDetailListUseCase
            ->handle(
                $context,
                $office,
                $providedIn,
                $sources
            )->toMap(fn (array $x): string => $x['cityCode']);
    }

    /**
     * 上限管理が自事業所であるか判定する.
     *
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Office\Office $office
     * @return bool
     */
    private function isSelfCoordination(DwsCertification $certification, Office $office): bool
    {
        return $certification->copayCoordination->copayCoordinationType === CopayCoordinationType::internal()
            && $certification->copayCoordination->officeId === $office->id;
    }

    /**
     * 指定された利用者IDsに対応するサービス詳細を削除する.
     *
     * @param \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @param array $userIds
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    private function removeDetailsForUsers(Seq $bundles, array $userIds): Seq
    {
        return $bundles->map(function (DwsBillingBundle $x) use ($userIds) {
            $details = Seq::fromArray($x->details)->filterNot(fn (DwsBillingServiceDetail $y) => in_array(
                $y->userId,
                $userIds,
                true
            ));
            return $this->bundleRepository->store($x->copy(['details' => $details->toArray()]));
        })->computed();
    }

    /**
     * 更新用の障害福祉サービス：明細書：上限管理区分を組み立てる
     *
     * @param \Domain\DwsCertification\DwsCertification $dwsCertification
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingCopayCoordination[]|\ScalikePHP\Option $copayCoordination
     * @param \Domain\Billing\DwsBillingStatement $updateStatement
     */
    private function buildUpdateCopayCoordinationStatus(
        DwsCertification $dwsCertification,
        Office $office,
        Option $copayCoordination,
        DwsBillingStatement $updateStatement
    ) {
        // 上限管理区分は以下の条件で更新する。
        // 上限管理事業所が自事業所の場合
        // - すでに既に利用者負担上限額管理結果票が存在する場合「入力中」
        // - 存在しない場合「未作成」
        // 上限管理事業所が自事業所以外の場合
        // - 変更しない
        if ($this->isSelfCoordination($dwsCertification, $office)) {
            return $copayCoordination->isEmpty()
                ? DwsBillingStatementCopayCoordinationStatus::uncreated()
                : DwsBillingStatementCopayCoordinationStatus::checking();
        } else {
            return $updateStatement->copayCoordinationStatus;
        }
    }

    /**
     * 障害福祉サービス：請求書を取得する.
     *
     * @param array|int[] $bundleIds
     * @return \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function findInvoices(array $bundleIds): Seq
    {
        $filterParams = [
            'dwsBillingBundleIds' => $bundleIds,
        ];
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->billingInvoiceFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * ログを出力する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $storedInvoices
     * @param \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $disusedInvoices
     * @param \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $updatedInvoices
     * @param \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements
     * @return void
     */
    private function outputLogs(Context $context, Seq $storedInvoices, Seq $disusedInvoices, Seq $updatedInvoices, Seq $statements): void
    {
        if ($storedInvoices->nonEmpty()) {
            $storedBundleIds = $storedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray();
            $storedInvoiceIds = $storedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray();
            $this->logger()->info(
                '障害福祉サービス：請求単位が登録されました',
                [
                    'id' => $storedBundleIds,
                ] + $context->logContext()
            );
            $this->logger()->info(
                '障害福祉サービス：請求書が登録されました',
                [
                    'id' => $storedInvoiceIds,
                ] + $context->logContext()
            );
        }
        if ($disusedInvoices->nonEmpty()) {
            $disusedBundleIds = $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray();
            $disusedInvoiceIds = $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray();
            $this->logger()->info(
                '障害福祉サービス：請求単位が削除されました',
                [
                    'id' => $disusedBundleIds,
                ] + $context->logContext()
            );
            $this->logger()->info(
                '障害福祉サービス：請求書が削除されました',
                [
                    'id' => $disusedInvoiceIds,
                ] + $context->logContext()
            );
        }
        $updatedBundleIds = $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray();
        $updatedInvoiceIds = $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray();
        $this->logger()->info(
            '障害福祉サービス：請求単位が更新されました',
            [
                'id' => $updatedBundleIds,
            ] + $context->logContext()
        );
        $this->logger()->info(
            '障害福祉サービス：請求書が更新されました',
            [
                'id' => $updatedInvoiceIds,
            ] + $context->logContext()
        );
        $this->logger()->info(
            '障害福祉サービス：明細書が更新されました',
            [
                'id' => $statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray(),
            ] + $context->logContext()
        );
    }

    /**
     * サービス提供年月を導出する.
     *
     * @param \ScalikePHP\Seq $bundles
     * @param array $bundleIds
     * @throws \Lib\Exceptions\LogicException
     * @return \Domain\Common\Carbon
     */
    private function resolveProvidedIn(Seq $bundles, array $bundleIds): Carbon
    {
        // 対象の利用者に紐づく請求単位
        $bundlesForTargetUsers = $bundles->filter(fn (DwsBillingBundle $x): bool => in_array($x->id, $bundleIds, true));

        // 請求単位の一覧に複数のサービス提供年月が含まれている場合は異常なケースなので例外とする
        if ($bundlesForTargetUsers->map(fn (DwsBillingBundle $x): string => $x->providedIn->toString())->distinct()->count() !== 1) {
            throw new LogicException('providedIns of DwsBillingBundles must be all the same month');
        }

        return $bundlesForTargetUsers->head()->providedIn;
    }
}
