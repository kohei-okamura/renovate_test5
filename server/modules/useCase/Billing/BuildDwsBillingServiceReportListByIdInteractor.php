<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Closure;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsHomeHelpServiceUnit;
use Domain\Billing\DwsVisitingCareForPwsdUnit;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;

/**
 * 障害福祉サービス：サービス提供実績記録票生成ユースケース実装.
 */
final class BuildDwsBillingServiceReportListByIdInteractor implements BuildDwsBillingServiceReportListByIdUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceReportListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsHomeHelpServiceUnitListUseCase $buildDwsHomeHelpServiceUnitListUseCase
     * @param \UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListUseCase $buildDwsVisitingCareForPwsdUnitListUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     */
    public function __construct(
        private BuildDwsHomeHelpServiceUnitListUseCase $buildDwsHomeHelpServiceUnitListUseCase,
        private BuildDwsVisitingCareForPwsdUnitListUseCase $buildDwsVisitingCareForPwsdUnitListUseCase,
        private IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        DwsProvisionReport $provisionReport,
        Option $previousProvisionReport,
        User $user,
        $isPreview = false
    ): Seq {
        $certification = $this->identifyDwsCertification($context, $provisionReport);

        return Seq::from(
            ...$this->buildDwsHomeHelpServiceReport(
                $context,
                $billingId,
                $bundleId,
                $user,
                $certification,
                $provisionReport,
                $previousProvisionReport,
                $isPreview
            ),
            ...$this->buildDwsVisitingCareForPwsdServiceReport(
                $context,
                $billingId,
                $bundleId,
                $user,
                $certification,
                $provisionReport,
                $isPreview
            ),
        );
    }

    /**
     * サービス提供実績記録票（重度訪問介護）を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param \Domain\User\User $user
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param bool $isPreview
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceReport[]&iterable
     */
    private function buildDwsVisitingCareForPwsdServiceReport(
        Context $context,
        int $billingId,
        int $bundleId,
        User $user,
        DwsCertification $certification,
        DwsProvisionReport $provisionReport,
        bool $isPreview
    ): iterable {
        $plans = $this->buildDwsVisitingCareForPwsdUnitListUseCase->handle(
            $context,
            $certification,
            $provisionReport,
            true
        );
        $results = $this->buildDwsVisitingCareForPwsdUnitListUseCase->handle(
            $context,
            $certification,
            $provisionReport,
            false
        );

        // ここで計算しないと明細が正しく生成されない.
        $items = Seq::from(...$this->buildDwsVisitingCareForPwsdServiceReportItemList($results, $plans));

        $count = fn (Closure $p): int => $items->filter($p)->count();
        // プレビュー版の場合は予定もしくは実績どちらかがあれば印字可能とする
        if ($results->nonEmpty() || $isPreview && $plans->nonEmpty()) {
            yield DwsBillingServiceReport::create([
                'dwsBillingId' => $billingId,
                'dwsBillingBundleId' => $bundleId,
                'user' => DwsBillingUser::from($user, $certification),
                'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
                'plan' => DwsBillingServiceReportAggregate::forVisitingCareForPwsd($plans),
                'result' => DwsBillingServiceReportAggregate::forVisitingCareForPwsd($results),
                'emergencyCount' => $count(fn (DwsBillingServiceReportItem $x): bool => $x->isEmergency),
                'firstTimeCount' => $count(fn (DwsBillingServiceReportItem $x): bool => $x->isFirstTime),
                'welfareSpecialistCooperationCount' => $count(
                    fn (DwsBillingServiceReportItem $x): bool => $x->isWelfareSpecialistCooperation
                ),
                'behavioralDisorderSupportCooperationCount' => $count(
                    fn (DwsBillingServiceReportItem $x): bool => $x->isBehavioralDisorderSupportCooperation
                ),
                'movingCareSupportCount' => $count(
                    fn (DwsBillingServiceReportItem $x): bool => $x->isMovingCareSupport
                ),
                'items' => $items->toArray(),
                'status' => DwsBillingStatus::ready(),
                'fixedAt' => null,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
        }
    }

    /**
     * サービス提供実績記録票明細一覧（重度訪問介護）を組み立てる.
     *
     * @param \ScalikePHP\Seq $result
     * @param \ScalikePHP\Seq $plan
     * @param int $serialNumber
     * @return iterable
     */
    private function buildDwsVisitingCareForPwsdServiceReportItemList(
        Seq $result,
        Seq $plan,
        int $serialNumber = 1
    ): iterable {
        if ($result->nonEmpty() && $plan->nonEmpty()) {
            $headResult = $result->head();
            $headPlan = $plan->head();
            assert($headResult instanceof DwsVisitingCareForPwsdUnit);
            assert($headPlan instanceof DwsVisitingCareForPwsdUnit);

            // 時間範囲・人数・サービスコード区分が一致していいる場合は計画と実績が一致していると判定する。
            $resultEqualsToPlan = $headResult->fragment->range->start->eq($headPlan->fragment->range->start)
                && $headResult->fragment->range->end->eq($headPlan->fragment->range->end)
                && $headResult->fragment->headcount === $headPlan->fragment->headcount
                && $headResult->category === $headPlan->category;
            if ($resultEqualsToPlan) {
                // 一連のサービスの最後の場合は提供通番を 1 増やす
                // 予定のみの場合は提供通番は変更しない
                yield $this->generateDwsVisitingCareForPwsdServiceReportItem($headResult, true, true, $serialNumber);
                yield from $this->buildDwsVisitingCareForPwsdServiceReportItemList(
                    $result->drop(1),
                    $plan->drop(1),
                    $headResult->isTerminated ? $serialNumber + 1 : $serialNumber
                );
            } elseif ($headPlan->fragment->range->start <= $headResult->fragment->range->start) {
                yield $this->generateDwsVisitingCareForPwsdServiceReportItem($headPlan, true, false, $serialNumber);
                yield from $this->buildDwsVisitingCareForPwsdServiceReportItemList(
                    $result,
                    $plan->drop(1),
                    $serialNumber
                );
            } else {
                yield $this->generateDwsVisitingCareForPwsdServiceReportItem($headResult, false, true, $serialNumber);
                yield from $this->buildDwsVisitingCareForPwsdServiceReportItemList(
                    $result->drop(1),
                    $plan,
                    $headResult->isTerminated ? $serialNumber + 1 : $serialNumber
                );
            }
        } elseif ($plan->nonEmpty()) {
            $headPlan = $plan->head();
            yield $this->generateDwsVisitingCareForPwsdServiceReportItem($headPlan, true, false, $serialNumber);
            yield from $this->buildDwsVisitingCareForPwsdServiceReportItemList($result, $plan->drop(1), $serialNumber);
        } elseif ($result->nonEmpty()) {
            $headResult = $result->head();
            yield $this->generateDwsVisitingCareForPwsdServiceReportItem($headResult, false, true, $serialNumber);
            yield from $this->buildDwsVisitingCareForPwsdServiceReportItemList(
                $result->drop(1),
                $plan,
                $headResult->isTerminated ? $serialNumber + 1 : $serialNumber
            );
        }
    }

    /**
     * サービス提供実績記録票明細（重度訪問介護）を生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdUnit $unit
     * @param bool $isPlan
     * @param bool $isResult
     * @param null|int $serialNumber
     * @return \Domain\Billing\DwsBillingServiceReportItem
     */
    private function generateDwsVisitingCareForPwsdServiceReportItem(
        DwsVisitingCareForPwsdUnit $unit,
        bool $isPlan,
        bool $isResult,
        ?int $serialNumber
    ): DwsBillingServiceReportItem {
        $duration = DwsBillingServiceReportDuration::create([
            'period' => $unit->fragment->range,
            'serviceDurationHours' => Decimal::fromInt($unit->getServiceDurationHours()),
            'movingDurationHours' => Decimal::fromInt($unit->getMovingDurationHours()),
        ]);
        return DwsBillingServiceReportItem::create([
            'serialNumber' => $serialNumber,
            'providedOn' => $unit->range->start,
            'serviceType' => DwsGrantedServiceCode::fromDwsServiceCodeCategory($unit->category),
            'providerType' => DwsBillingServiceReportProviderType::none(),
            'situation' => DwsBillingServiceReportSituation::none(),
            'plan' => $isPlan ? $duration : null,
            'result' => $isResult ? $duration : null,
            'serviceCount' => $unit->serviceCount,
            'headcount' => $unit->fragment->headcount,
            'isCoaching' => $unit->fragment->isCoaching,
            'isFirstTime' => $unit->isFirst,
            'isEmergency' => $unit->isEmergency,
            'isWelfareSpecialistCooperation' => false,
            'isBehavioralDisorderSupportCooperation' => $unit->isBehavioralDisorderSupportCooperation,
            // 移動介護緊急時支援加算は4月改訂で増えたが一旦スコープ外で未対応なためfalse
            'isMovingCareSupport' => false,
            'isDriving' => false,
            // 日跨ぎ・月跨ぎは一旦無視する。
            'isPreviousMonth' => false,
            // 備考は一旦無視
            'note' => '',
        ]);
    }

    /**
     * サービス提供実績記録票（居宅）を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param \Domain\User\User $user
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option $previousProvisionReport
     * @param bool $isPreview
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceReport[]&iterable
     */
    private function buildDwsHomeHelpServiceReport(
        Context $context,
        int $billingId,
        int $bundleId,
        User $user,
        DwsCertification $certification,
        DwsProvisionReport $provisionReport,
        Option $previousProvisionReport,
        bool $isPreview
    ): iterable {
        $plans = $this->buildDwsHomeHelpServiceUnitListUseCase->handle(
            $context,
            $certification,
            $provisionReport,
            $previousProvisionReport,
            true
        );
        $results = $this->buildDwsHomeHelpServiceUnitListUseCase->handle(
            $context,
            $certification,
            $provisionReport,
            $previousProvisionReport,
            false
        );

        // ここで計算しないと明細が正しく生成されない.
        $items = Seq::from(...$this->buildDwsHomeHelpServiceReportItemList($results, $plans, $provisionReport->providedIn));
        // プレビュー版の場合は予定もしくは実績どちらかがあれば印字可能とする
        if ($results->nonEmpty() || $isPreview && $plans->nonEmpty()) {
            yield DwsBillingServiceReport::create([
                'dwsBillingId' => $billingId,
                'dwsBillingBundleId' => $bundleId,
                'user' => DwsBillingUser::from($user, $certification),
                'format' => DwsBillingServiceReportFormat::homeHelpService(),
                'plan' => DwsBillingServiceReportAggregate::forHomeHelpService($plans),
                'result' => DwsBillingServiceReportAggregate::forHomeHelpService($results),
                'movingDuration' => 0,
                'emergencyCount' => $items
                    ->filter(fn (DwsBillingServiceReportItem $x): bool => $x->isEmergency)
                    ->count(),
                'firstTimeCount' => $items
                    ->filter(fn (DwsBillingServiceReportItem $x): bool => $x->isFirstTime)
                    ->count(),
                'welfareSpecialistCooperationCount' => $items
                    ->filter(fn (DwsBillingServiceReportItem $x): bool => $x->isWelfareSpecialistCooperation)
                    ->count(),
                'behavioralDisorderSupportCooperationCount' => $items
                    ->filter(fn (DwsBillingServiceReportItem $x): bool => $x->isBehavioralDisorderSupportCooperation)
                    ->count(),
                'movingCareSupportCount' => $items
                    ->filter(fn (DwsBillingServiceReportItem $x): bool => $x->isMovingCareSupport)
                    ->count(),
                'items' => $items->toArray(),
                'status' => DwsBillingStatus::ready(),
                'fixedAt' => null,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
        }
    }

    /**
     * サービス提供実績記録票：明細一覧（居宅）を組み立てる.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceUnit[]&\ScalikePHP\Seq $results
     * @param \Domain\Billing\DwsHomeHelpServiceUnit[]&\ScalikePHP\Seq $plans
     * @param \Domain\Common\Carbon $providedIn
     * @param int $serialNumber 提供通番
     * @return \Domain\Billing\DwsBillingServiceReportItem[]&iterable
     */
    private function buildDwsHomeHelpServiceReportItemList(
        Seq $results,
        Seq $plans,
        Carbon $providedIn,
        int $serialNumber = 1
    ): iterable {
        if ($results->nonEmpty() && $plans->nonEmpty()) {
            $headResult = $results->head();
            $headPlan = $plans->head();
            assert($headResult instanceof DwsHomeHelpServiceUnit);
            assert($headPlan instanceof DwsHomeHelpServiceUnit);
            if ($headResult->fragment->range->start->eq($headPlan->fragment->range->start)
                && $headResult->fragment->range->end->eq($headPlan->fragment->range->end)
                && $headResult->fragment->headcount === $headPlan->fragment->headcount
                && $headResult->category === $headPlan->category
            ) {
                // 予定と実績が一致していた場合は予定と実績両方
                // 一連のサービスの最後の場合は提供通番を 1 増やす
                // 予定のみの場合は提供通番は変更しない
                yield $this->generateDwsHomeHelpServiceReportItem($headResult, true, true, $providedIn, $serialNumber);
                yield from $this->buildDwsHomeHelpServiceReportItemList(
                    $results->drop(1),
                    $plans->drop(1),
                    $providedIn,
                    $headResult->isTerminated ? $serialNumber + 1 : $serialNumber
                );
            } elseif ($headPlan->fragment->range->start->lte($headResult->fragment->range->start)) {
                yield $this->generateDwsHomeHelpServiceReportItem($headPlan, true, false, $providedIn, $serialNumber);
                yield from $this->buildDwsHomeHelpServiceReportItemList(
                    $results,
                    $plans->drop(1),
                    $providedIn,
                    $serialNumber
                );
            } else {
                yield $this->generateDwsHomeHelpServiceReportItem($headResult, false, true, $providedIn, $serialNumber);
                yield from $this->buildDwsHomeHelpServiceReportItemList(
                    $results->drop(1),
                    $plans,
                    $providedIn,
                    $headResult->isTerminated ? $serialNumber + 1 : $serialNumber
                );
            }
        } elseif ($plans->nonEmpty()) {
            $headPlan = $plans->head();
            assert($headPlan instanceof DwsHomeHelpServiceUnit);
            yield $this->generateDwsHomeHelpServiceReportItem($headPlan, true, false, $providedIn, $serialNumber);
            yield from $this->buildDwsHomeHelpServiceReportItemList(
                $results,
                $plans->drop(1),
                $providedIn,
                $serialNumber
            );
        } elseif ($results->nonEmpty()) {
            $headResult = $results->head();
            assert($headResult instanceof DwsHomeHelpServiceUnit);
            yield $this->generateDwsHomeHelpServiceReportItem($headResult, false, true, $providedIn, $serialNumber);
            yield from $this->buildDwsHomeHelpServiceReportItemList(
                $results->drop(1),
                $plans,
                $providedIn,
                $headResult->isTerminated ? $serialNumber + 1 : $serialNumber
            );
        }
    }

    /**
     * サービス提供実績記録票：明細（居宅）を生成する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceUnit $unit
     * @param bool $isPlan
     * @param bool $isResult
     * @param \Domain\Common\Carbon $providedIn
     * @param null|int $serialNumber
     * @return \Domain\Billing\DwsBillingServiceReportItem
     */
    private function generateDwsHomeHelpServiceReportItem(
        DwsHomeHelpServiceUnit $unit,
        bool $isPlan,
        bool $isResult,
        Carbon $providedIn,
        ?int $serialNumber
    ): DwsBillingServiceReportItem {
        $duration = DwsBillingServiceReportDuration::create([
            'period' => $unit->fragment->range,
            'serviceDurationHours' => Decimal::fromInt($unit->getServiceDurationHours()),
            'movingDurationHours' => Decimal::zero(),
        ]);

        return DwsBillingServiceReportItem::create([
            'serialNumber' => $serialNumber,
            'providedOn' => $unit->range->start,
            'serviceType' => DwsGrantedServiceCode::fromDwsServiceCodeCategory($unit->category),
            'providerType' => $unit->fragment->providerType->toDwsBillingServiceReportProviderType(),
            'situation' => DwsBillingServiceReportSituation::none(),
            'plan' => $isPlan ? $duration : null,
            'result' => $isResult ? $duration : null,
            'serviceCount' => $unit->fragment->headcount === 2 ? ($unit->fragment->isSecondary ? 2 : 1) : 0,
            'headcount' => $unit->fragment->headcount,
            'isCoaching' => false,
            'isFirstTime' => $unit->isFirst,
            'isEmergency' => $unit->isEmergency,
            'isWelfareSpecialistCooperation' => false,
            'isBehavioralDisorderSupportCooperation' => false,
            'isMovingCareSupport' => false,
            'isDriving' => false,
            'isPreviousMonth' => $unit->range->start->lt($providedIn->startOfMonth()),
            // 備考は一旦無視
            'note' => '',
        ]);
    }

    /**
     * 受給者証を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyDwsCertification(Context $context, DwsProvisionReport $report): DwsCertification
    {
        $userId = $report->userId;
        $providedIn = $report->providedIn;
        return $this->identifyDwsCertificationUseCase
            ->handle($context, $userId, $providedIn)
            ->getOrElse(function () use ($userId, $providedIn): void {
                $date = $providedIn->toDateString();
                throw new NotFoundException("DwsCertification for User({$userId}) at {$date} not found");
            });
    }
}
