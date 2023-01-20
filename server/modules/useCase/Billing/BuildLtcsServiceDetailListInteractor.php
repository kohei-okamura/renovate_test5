<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use Domain\User\User;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\User\IdentifyUserLtcsCalcSpecUseCase;

/**
 * 介護保険サービス：請求：サービス詳細一覧組み立てユースケース実装.
 */
final class BuildLtcsServiceDetailListInteractor implements BuildLtcsServiceDetailListUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /**
     * {@link \UseCase\Billing\BuildLtcsServiceDetailListInteractor} constructor.
     *
     * @param \UseCase\User\IdentifyUserLtcsCalcSpecUseCase $identifyUserLtcsCalcSpecUseCase
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder $dictionaryEntryFinder
     * @param \UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase $covid19PandemicSpecialAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingLocationAdditionUseCase $computeLocationAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase $computeFirstTimeAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase $computeTreatmentImprovementAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase $computeSpecifiedTreatmentImprovementAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase $computeEmergencyAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase $computeVitalFunctionsImprovementAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingUserLocationAdditionUseCase $computeLtcsBillingUserLocationAdditionUseCase
     * @param \UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase $computeLtcsBillingBaseIncreaseSupportAdditionUseCase
     */
    public function __construct(
        private readonly IdentifyUserLtcsCalcSpecUseCase $identifyUserLtcsCalcSpecUseCase,
        private readonly LtcsHomeVisitLongTermCareDictionaryEntryFinder $dictionaryEntryFinder,
        private readonly ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase $covid19PandemicSpecialAdditionUseCase,
        private readonly ComputeLtcsBillingLocationAdditionUseCase $computeLocationAdditionUseCase,
        private readonly ComputeLtcsBillingFirstTimeAdditionUseCase $computeFirstTimeAdditionUseCase,
        private readonly ComputeLtcsBillingTreatmentImprovementAdditionUseCase $computeTreatmentImprovementAdditionUseCase,
        private readonly ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase $computeSpecifiedTreatmentImprovementAdditionUseCase,
        private readonly ComputeLtcsBillingEmergencyAdditionUseCase $computeEmergencyAdditionUseCase,
        private readonly ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase $computeVitalFunctionsImprovementAdditionUseCase,
        private readonly ComputeLtcsBillingUserLocationAdditionUseCase $computeLtcsBillingUserLocationAdditionUseCase,
        private readonly ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase $computeLtcsBillingBaseIncreaseSupportAdditionUseCase,
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Carbon $providedIn, Seq $reports, Seq $users, bool $usePlan = false): array
    {
        $dictionaryEntries = $this->findDictionaryEntries($providedIn);
        return $reports
            ->flatMap(fn (LtcsProvisionReport $x): iterable => $this->generate(
                $context,
                $x,
                $dictionaryEntries,
                $usePlan,
                $users,
                $providedIn
            ))
            ->toArray();
    }

    /**
     * 辞書エントリの一覧を取得する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq
     */
    private function findDictionaryEntries(Carbon $providedIn): Seq
    {
        $filterParams = ['providedIn' => $providedIn];
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->dictionaryEntryFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 介護保険サービス：予実からサービス詳細の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param bool $usePlan
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\Common\Carbon $providedIn
     * @throws \Exception
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&iterable
     */
    private function generate(
        Context $context,
        LtcsProvisionReport $report,
        Seq $dictionaryEntries,
        bool $usePlan,
        Seq $users,
        Carbon $providedIn
    ): iterable {
        $main = Seq::from(...$this->generateMain($report, $dictionaryEntries, $usePlan));
        if ($main->nonEmpty()) {
            /** @var \Domain\User\User $user */
            $user = $users
                ->find(fn (User $x): bool => $x->id === $report->userId)
                ->getOrElse(function (): never {
                    throw new LogicException('user Not Found');
                });
            $userSpec = $this->identifyUserLtcsCalcSpec($context, $user, $providedIn->lastOfMonth());
            $totalMainScore = $main->map(fn (LtcsBillingServiceDetail $x): int => $x->totalScore)->sum();
            $wholeMainScore = $main->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)->sum();
            yield from $main;
            yield from $this->generateAdditions(
                $context,
                $report,
                $dictionaryEntries,
                $totalMainScore,
                $wholeMainScore,
                $usePlan,
                $userSpec
            );
        }
    }

    /**
     * 本体サービス分（※）のサービス詳細一覧を生成する.
     *
     * ※本体サービス分＝加算等ではない、サービスの提供そのものを表すサービス詳細.
     *
     * ## 回数・サービス単位数について
     * 当面の間は回数を固定で 1 とする.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param bool $usePlan
     * @throws \Exception
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&iterable
     */
    private function generateMain(LtcsProvisionReport $report, Seq $dictionaryEntries, bool $usePlan): iterable
    {
        $serviceCodeMap = $dictionaryEntries->toMap(function (LtcsHomeVisitLongTermCareDictionaryEntry $x): string {
            return $x->serviceCode->toString();
        });
        foreach ($report->entries as $entry) {
            $serviceCode = $entry->serviceCode;
            // 自費サービスの場合はサービスコードがないため除外
            if ($serviceCode === null) {
                continue;
            }

            $serviceCodeString = $serviceCode->toString();

            /** @var LtcsHomeVisitLongTermCareDictionaryEntry $source */
            $source = $serviceCodeMap->getOrElse($serviceCodeString, function () use ($serviceCodeString): void {
                throw new SetupException("Entry for {$serviceCodeString} not found in dictionary");
            });

            $serviceCodeCategory = $source->category;
            $buildingSubtraction = $entry->buildingSubtraction();
            $noteRequirement = $source->noteRequirement;
            $durationMinutes = Seq::from(...$entry->amounts)
                ->map(fn (LtcsProjectAmount $x): int => $x->amount)
                ->sum();
            $physicalMinutes = Seq::from(...$entry->amounts)
                ->filter(fn (LtcsProjectAmount $x): bool => $x->category === LtcsProjectAmountCategory::physicalCare())
                ->map(fn (LtcsProjectAmount $x): int => $x->amount)
                ->sum();
            $unitScore = $this->computeUnitScore($source, $physicalMinutes, $entry->headcount);
            $target = $usePlan ? $entry->plans : $entry->results;
            foreach ($target as $providedOn) {
                yield $this->createServiceDetail(
                    $report,
                    $providedOn,
                    $serviceCode,
                    $serviceCodeCategory,
                    $buildingSubtraction,
                    $noteRequirement,
                    false,
                    $source->isLimited,
                    $durationMinutes,
                    $unitScore,
                    $unitScore,
                );
            }
        }
    }

    /**
     * 各種加算のサービス詳細一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param int $totalMainScore
     * @param int $wholeMainScore
     * @param bool $usePlan
     * @param \Domain\User\UserLtcsCalcSpec[]&\ScalikePHP\Option $userSpec
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&iterable
     */
    private function generateAdditions(
        Context $context,
        LtcsProvisionReport $report,
        Seq $dictionaryEntries,
        int $totalMainScore,
        int $wholeMainScore,
        bool $usePlan,
        Option $userSpec
    ): iterable {
        // 同一建物減算
        // TODO: 初回リリースでは不要。DEV-5568

        // 令和3年9月30日までの上乗せ分
        $covid19PandemicSpecialAddition = $this->covid19PandemicSpecialAdditionUseCase->handle(
            $report,
            $dictionaryEntries,
            $totalMainScore,
        );
        $covid19PandemicSpecialAdditionScore = $covid19PandemicSpecialAddition
            ->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)
            ->sum();
        yield from $covid19PandemicSpecialAddition;

        // 訪問介護中山間地域等提供加算（利用者別地域加算）
        $userLocationAddition = $this->computeLtcsBillingUserLocationAdditionUseCase->handle(
            $context,
            $report,
            $userSpec,
            $dictionaryEntries,
            $totalMainScore + $covid19PandemicSpecialAdditionScore,
            $usePlan,
        );
        $userLocationAdditionScore = $userLocationAddition
            ->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)
            ->sum();
        yield from $userLocationAddition;

        // 地域加算
        $locationAddition = $this->computeLocationAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries,
            $totalMainScore + $covid19PandemicSpecialAdditionScore,
            $usePlan
        );
        $locationAdditionScore = $locationAddition
            ->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)
            ->sum();
        yield from $locationAddition;

        // 緊急時訪問介護加算
        $emergencyAddition = $this->computeEmergencyAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries,
            $usePlan
        );
        $emergencyAdditionScore = $emergencyAddition
            ->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)
            ->sum();
        yield from $emergencyAddition;

        // 初回加算
        $firstTimeAddition = $this->computeFirstTimeAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries,
            $usePlan
        );
        $firstTimeAdditionScore = $firstTimeAddition
            ->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)
            ->sum();
        yield from $firstTimeAddition;

        // 生活機能向上連携加算
        $vitalFunctionsImprovementAddition = $this->computeVitalFunctionsImprovementAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries
        );
        $vitalFunctionsImprovementAdditionScore = $vitalFunctionsImprovementAddition
            ->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)
            ->sum();
        yield from $vitalFunctionsImprovementAddition;

        // 介護職員処遇改善加算・介護職員等特定処遇改善加算
        $baseScore = $wholeMainScore
            + $locationAdditionScore
            + $emergencyAdditionScore
            + $firstTimeAdditionScore
            + $vitalFunctionsImprovementAdditionScore
            + $covid19PandemicSpecialAdditionScore
            + $userLocationAdditionScore;
        $excessScore = $usePlan ? $report->plan : $report->result;
        $scoreWithinMaxBenefitQuota = $excessScore->maxBenefitQuotaExcessScore
            + $userLocationAddition->map(fn (LtcsBillingServiceDetail $x): int => $x->maxBenefitQuotaExcessScore)->sum()
            + $locationAddition->map(fn (LtcsBillingServiceDetail $x): int => $x->maxBenefitQuotaExcessScore)->sum();
        $scoreWithinMaxBenefit = $excessScore->maxBenefitExcessScore
            + $userLocationAddition->map(fn (LtcsBillingServiceDetail $x): int => $x->maxBenefitExcessScore)->sum()
            + $locationAddition->map(fn (LtcsBillingServiceDetail $x): int => $x->maxBenefitExcessScore)->sum();
        yield from $this->computeTreatmentImprovementAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries,
            $baseScore,
            $scoreWithinMaxBenefitQuota,
            $scoreWithinMaxBenefit,
        );
        yield from $this->computeSpecifiedTreatmentImprovementAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries,
            $baseScore,
            $scoreWithinMaxBenefitQuota,
            $scoreWithinMaxBenefit,
        );
        yield from $this->computeLtcsBillingBaseIncreaseSupportAdditionUseCase->handle(
            $context,
            $report,
            $dictionaryEntries,
            $baseScore,
            $scoreWithinMaxBenefitQuota,
            $scoreWithinMaxBenefit,
        );
    }

    /**
     * 介護保険サービス：利用者別算定情報を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\User\UserLtcsCalcSpec[]&\ScalikePHP\Option
     */
    private function identifyUserLtcsCalcSpec(Context $context, User $user, Carbon $targetDate): Option
    {
        return $this->identifyUserLtcsCalcSpecUseCase->handle($context, $user, $targetDate);
    }
}
