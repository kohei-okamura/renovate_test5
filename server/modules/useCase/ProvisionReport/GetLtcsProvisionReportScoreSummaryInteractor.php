<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use UseCase\Billing\BuildLtcsServiceDetailListUseCase;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 介護保険サービス：予実合計単位数取得ユースケース実装.
 */
class GetLtcsProvisionReportScoreSummaryInteractor implements GetLtcsProvisionReportScoreSummaryUseCase
{
    /**
     * constructor.
     *
     * @param \UseCase\Billing\BuildLtcsServiceDetailListUseCase $buildLtcsServiceDetailListUseCase
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        private BuildLtcsServiceDetailListUseCase $buildLtcsServiceDetailListUseCase,
        private IdentifyContractUseCase $identifyContractUseCase,
        private LookupUserUseCase $lookupUserUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Seq $entries,
        HomeVisitLongTermCareSpecifiedOfficeAddition $homeVisitLongTermCareSpecifiedOfficeAddition,
        LtcsTreatmentImprovementAddition $ltcsTreatmentImprovementAddition,
        LtcsSpecifiedTreatmentImprovementAddition $ltcsSpecifiedTreatmentImprovementAddition,
        LtcsBaseIncreaseSupportAddition $baseIncreaseSupportAddition,
        LtcsOfficeLocationAddition $ltcsOfficeLocationAddition,
        LtcsProvisionReportOverScore $plan,
        LtcsProvisionReportOverScore $result,
    ): array {
        $provisionReport = $this->buildLtcsProvisionReport(
            $context,
            $entries,
            $officeId,
            $userId,
            $providedIn,
            $homeVisitLongTermCareSpecifiedOfficeAddition,
            $ltcsTreatmentImprovementAddition,
            $ltcsSpecifiedTreatmentImprovementAddition,
            $baseIncreaseSupportAddition,
            $ltcsOfficeLocationAddition,
            $plan,
            $result
        );
        $user = $this->lookupUser($context, $provisionReport->userId);
        $serviceDetailFromResult = $this->buildLtcsServiceDetailListUseCase->handle(
            $context,
            $providedIn,
            Seq::from($provisionReport),
            Seq::from($user),
            false
        );
        $serviceDetailFromPlan = $this->buildLtcsServiceDetailListUseCase->handle(
            $context,
            $providedIn,
            Seq::from($provisionReport),
            Seq::from($user),
            true
        );

        $result = $this->calculateManagedAndUnmanagedScores($serviceDetailFromResult);
        $plan = $this->calculateManagedAndUnmanagedScores($serviceDetailFromPlan);

        return compact('plan', 'result');
    }

    /**
     * 介護保険サービス：予実を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReportEntry[]&\ScalikePHP\Seq $entries
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @param HomeVisitLongTermCareSpecifiedOfficeAddition $homeVisitLongTermCareSpecifiedOfficeAddition
     * @param LtcsTreatmentImprovementAddition $ltcsTreatmentImprovementAddition
     * @param LtcsSpecifiedTreatmentImprovementAddition $ltcsSpecifiedTreatmentImprovementAddition
     * @param LtcsBaseIncreaseSupportAddition $baseIncreaseSupportAddition
     * @param LtcsOfficeLocationAddition $ltcsOfficeLocationAddition
     * @param LtcsProvisionReportOverScore $plan
     * @param LtcsProvisionReportOverScore $result
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function buildLtcsProvisionReport(
        Context $context,
        Seq $entries,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        HomeVisitLongTermCareSpecifiedOfficeAddition $homeVisitLongTermCareSpecifiedOfficeAddition,
        LtcsTreatmentImprovementAddition $ltcsTreatmentImprovementAddition,
        LtcsSpecifiedTreatmentImprovementAddition $ltcsSpecifiedTreatmentImprovementAddition,
        LtcsBaseIncreaseSupportAddition $baseIncreaseSupportAddition,
        LtcsOfficeLocationAddition $ltcsOfficeLocationAddition,
        LtcsProvisionReportOverScore $plan,
        LtcsProvisionReportOverScore $result,
    ): LtcsProvisionReport {
        $contract = $this->getContract($context, $officeId, $userId);
        return LtcsProvisionReport::create([
            'officeId' => $officeId,
            'userId' => $userId,
            'contractId' => $contract->id,
            'providedIn' => Carbon::parse($providedIn),
            'entries' => $entries->toArray(),
            'status' => LtcsProvisionReportStatus::inProgress(),
            'specifiedOfficeAddition' => $homeVisitLongTermCareSpecifiedOfficeAddition,
            'treatmentImprovementAddition' => $ltcsTreatmentImprovementAddition,
            'specifiedTreatmentImprovementAddition' => $ltcsSpecifiedTreatmentImprovementAddition,
            'baseIncreaseSupportAddition' => $baseIncreaseSupportAddition,
            'locationAddition' => $ltcsOfficeLocationAddition,
            'plan' => $plan,
            'result' => $result,
        ]);
    }

    /**
     * 契約を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @return \Domain\Contract\Contract
     */
    private function getContract(Context $context, int $officeId, int $userId): Contract
    {
        return $this->identifyContractUseCase
            ->handle(
                $context,
                Permission::updateLtcsProvisionReports(),
                $officeId,
                $userId,
                ServiceSegment::longTermCare(),
                Carbon::now()
            )
            ->getOrElse(function (): void {
                throw new NotFoundException('Contract not found');
            });
    }

    /**
     * 「限度額管理対象単位数」と「限度額管理対象外単位数」を計算する
     *
     * @param array|\Domain\Billing\LtcsBillingServiceDetail[] $details
     * @return array
     */
    private function calculateManagedAndUnmanagedScores(array $details): array
    {
        // このユースケースでは諸々の計算をクライアント側（フロントエンド）でやる想定のため
        // 限度基準を超える単位数は 0 を指定する
        [$managedScore, $unmanagedScore] = LtcsBillingServiceDetail::aggregateScore(
            details: Seq::fromArray($details),
            excessScore: 0
        );
        return [
            'managedScore' => $managedScore,
            'unmanagedScore' => $unmanagedScore,
        ];
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateLtcsProvisionReports(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }
}
