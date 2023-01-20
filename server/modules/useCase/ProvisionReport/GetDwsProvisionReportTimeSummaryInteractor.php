<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsProvisionReportTimeSummaryItem;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Generator;
use Lib\Arrays;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListUseCase;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListUseCase;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;

/**
 * 障害福祉サービス：予実合計時間数取得ユースケース実装.
 */
final class GetDwsProvisionReportTimeSummaryInteractor implements GetDwsProvisionReportTimeSummaryUseCase
{
    private BuildDwsHomeHelpServiceUnitListUseCase $buildDwsHomeHelpServiceUnitListUseCase;
    private BuildDwsVisitingCareForPwsdUnitListUseCase $buildDwsVisitingCareForPwsdUnitListUseCase;
    private IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase;

    /**
     * {@link \UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsHomeHelpServiceUnitListUseCase $buildDwsHomeHelpServiceUnitListUseCase
     * @param \UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListUseCase $buildDwsVisitingCareForPwsdUnitListUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     */
    public function __construct(
        BuildDwsHomeHelpServiceUnitListUseCase $buildDwsHomeHelpServiceUnitListUseCase,
        BuildDwsVisitingCareForPwsdUnitListUseCase $buildDwsVisitingCareForPwsdUnitListUseCase,
        IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
    ) {
        $this->buildDwsHomeHelpServiceUnitListUseCase = $buildDwsHomeHelpServiceUnitListUseCase;
        $this->buildDwsVisitingCareForPwsdUnitListUseCase = $buildDwsVisitingCareForPwsdUnitListUseCase;
        $this->identifyDwsCertificationUseCase = $identifyDwsCertificationUseCase;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Seq $plans,
        Seq $results
    ): array {
        $certification = $this->identifyDwsCertification($context, $userId, $providedIn);
        $report = $this->buildDwsProvisionReport($userId, $officeId, $providedIn, $plans, $results);
        return [
            'plan' => $this->buildSummaryItem($context, $certification, $report, true),
            'result' => $this->buildSummaryItem($context, $certification, $report, false),
        ];
    }

    /**
     * 受給者証を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyDwsCertification(Context $context, int $userId, Carbon $providedIn): DwsCertification
    {
        return $this->identifyDwsCertificationUseCase
            ->handle($context, $userId, $providedIn)
            ->getOrElse(function () use ($userId, $providedIn): void {
                $date = $providedIn->toDateString();
                throw new NotFoundException("DwsCertification for User({$userId}) at {$date} not found");
            });
    }

    /**
     * 障害福祉サービス：予実を組み立てる.
     *
     * @param int $userId
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]|\ScalikePHP\Seq $plans
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]|\ScalikePHP\Seq $results
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    private function buildDwsProvisionReport(
        int $userId,
        int $officeId,
        Carbon $providedIn,
        Seq $plans,
        Seq $results
    ): DwsProvisionReport {
        // 契約は今回使用しないので本当は取得してちゃんとしておきたいが、時間がないので今回は取得しないでおく。
        // 各時間も不要なので適当な値
        return DwsProvisionReport::create([
            'id' => null,
            'userId' => $userId,
            'officeId' => $officeId,
            'contractId' => null,
            'providedIn' => $providedIn,
            'plans' => $plans->toArray(),
            'results' => $results->toArray(),
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::now(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * 障害福祉サービス：予実詳細：合計を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param bool $forPlan
     * @return \Domain\Billing\DwsProvisionReportTimeSummaryItem
     */
    private function buildSummaryItem(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $report,
        bool $forPlan
    ): DwsProvisionReportTimeSummaryItem {
        $assoc = Arrays::generate(function () use ($context, $certification, $report, $forPlan): Generator {
            $homeHelpServiceAggregate = DwsBillingServiceReportAggregate::forHomeHelpService(
                $this->buildDwsHomeHelpServiceUnitListUseCase->handle(
                    $context,
                    $certification,
                    $report,
                    Option::none(),
                    $forPlan
                )
            );
            $visitingCareForPwsdAggregate = DwsBillingServiceReportAggregate::forVisitingCareForPwsd(
                $this->buildDwsVisitingCareForPwsdUnitListUseCase->handle($context, $certification, $report, $forPlan)
            );
            yield from self::extract($homeHelpServiceAggregate, [
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateGroup::accompany(),
            ]);
            yield from self::extract($visitingCareForPwsdAggregate, [
                DwsBillingServiceReportAggregateGroup::visitingCareForPwsd(),
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd(),
            ]);
        });
        return DwsProvisionReportTimeSummaryItem::fromAssoc($assoc);
    }

    /**
     * サービス提供実績記録票：合計から合計時間を抽出する.
     *
     * @param \Domain\Billing\DwsBillingServiceReportAggregate $aggregate
     * @param array $groups
     * @return iterable
     */
    private static function extract(DwsBillingServiceReportAggregate $aggregate, array $groups): iterable
    {
        foreach ($groups as $group) {
            assert($group instanceof DwsBillingServiceReportAggregateGroup);
            yield $group->value() => $aggregate->get($group, DwsBillingServiceReportAggregateCategory::categoryTotal());
        }
    }
}
