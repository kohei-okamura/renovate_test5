<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetail as ServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition as Disposition;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatement as Statement;
use Domain\Billing\LtcsBillingStatementAggregate as Aggregate;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy as AggregateSubsidy;
use Domain\Billing\LtcsBillingStatementInsurance as Insurance;
use Domain\Billing\LtcsBillingStatementItem as Item;
use Domain\Billing\LtcsBillingStatementItemSubsidy as ItemSubsidy;
use Domain\Billing\LtcsBillingStatementSubsidy as Subsidy;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsBillingUser;
use Domain\Billing\LtcsCarePlanAuthor;
use Domain\Billing\LtcsExpiredReason;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractFinder;
use Domain\Contract\ContractStatus;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\User\User;
use Domain\User\UserLtcsSubsidy;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Lib\Math;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase;
use UseCase\User\IdentifyUserLtcsSubsidyUseCase;

/**
 * 介護保険サービス：明細書組み立てユースケース実装.
 */
final class BuildLtcsBillingStatementInteractor implements BuildLtcsBillingStatementUseCase
{
    private const MAX_SUBSIDIES = 3;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingStatementListInteractor} constructor.
     *
     * @param \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase $buildAppendixUseCase
     * @param \UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase $identifyLtcsInsCardUseCase
     * @param \UseCase\User\IdentifyUserLtcsSubsidyUseCase $identifyUserLtcsSubsidyUseCase
     * @param \UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase $resolveLtcsNameFromServiceCodesUseCase
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\Contract\ContractFinder $contractFinder
     */
    public function __construct(
        private readonly BuildLtcsProvisionReportSheetAppendixUseCase $buildAppendixUseCase,
        private readonly IdentifyLtcsInsCardUseCase $identifyLtcsInsCardUseCase,
        private readonly IdentifyUserLtcsSubsidyUseCase $identifyUserLtcsSubsidyUseCase,
        private readonly ResolveLtcsNameFromServiceCodesUseCase $resolveLtcsNameFromServiceCodesUseCase,
        private readonly OfficeRepository $officeRepository,
        private readonly ContractFinder $contractFinder
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        LtcsBillingBundle $bundle,
        User $user,
        Office $office,
        Seq $details,
        Decimal $unitCost,
        Seq $reports
    ): LtcsBillingStatement {
        $providedIn = $bundle->providedIn->startOfMonth();
        $contract = $this->identifyContract($bundle, $office, $user);
        $insCardAtFirstOfMonth = $this->identifyLtcsInsCardUseCase
            ->handle($context, $user, $providedIn);
        $insCardAtLastOfMonth = $this->identifyLtcsInsCardUseCase
            ->handle($context, $user, $providedIn->endOfMonth())
            ->getOrElse(function () use ($providedIn, $user): void {
                throw new NotFoundException("LtcsInsCard for User({$user->id}) in {$providedIn->lastOfMonth()->toDateString()} not found");
            });

        /** @var \Domain\ProvisionReport\LtcsProvisionReport $report */
        $report = $reports
            ->find(function (LtcsProvisionReport $x) use ($providedIn, $user): bool {
                return $providedIn->isSameMonth($x->providedIn) && $x->userId === $user->id;
            })
            ->getOrElse(function (): never {
                throw new LogicException('ProvisionReport must be found');
            });

        $periodStart = $contract->ltcsPeriod->start;
        $periodEnd = $contract->ltcsPeriod->end;
        $isStartedInTheMonth = $periodStart !== null && $periodStart->startOfMonth()->equalTo($providedIn);
        $isEndedInTheMonth = $periodEnd !== null && $periodEnd->startOfMonth()->equalTo($providedIn);

        // DEV-6067 の対応により `disposition` が `result` 以外のサービス詳細は含まれないはず
        // 古いデータに対応するため、念のためフィルタする
        $results = $details
            ->filter(fn (ServiceDetail $x): bool => $x->disposition === Disposition::result())
            ->computed();

        $userSubsidies = $this->identifyUserLtcsSubsidyUseCase->handle($context, $user, $bundle->providedIn);
        $benefitRate = 100 - $insCardAtLastOfMonth->copayRate;
        $aggregates = $this->buildAggregates(
            details: $results,
            userSubsidies: $userSubsidies,
            unitCost: $unitCost,
            benefitRate: $benefitRate,
            excessScore: $report->result->sum()
        );
        $items = $this->buildItems($results, $userSubsidies);

        return new Statement(
            id: null,
            billingId: $bundle->billingId,
            bundleId: $bundle->id,
            insurerNumber: $insCardAtLastOfMonth->insurerNumber,
            insurerName: $insCardAtLastOfMonth->insurerName,
            user: LtcsBillingUser::from($user, $insCardAtLastOfMonth),
            carePlanAuthor: $this->buildCarePlanAuthor($context, $insCardAtLastOfMonth),
            agreedOn: $isStartedInTheMonth ? $periodStart : null,
            expiredOn: $isEndedInTheMonth ? $periodEnd : null,
            expiredReason: $isEndedInTheMonth ? $contract->expiredReason : LtcsExpiredReason::unspecified(),
            insurance: new Insurance(
                benefitRate: $benefitRate,
                totalScore: $items->map(fn (Item $x): int => $x->totalScore)->sum(),
                claimAmount: $aggregates->map(fn (Aggregate $x): int => $x->insurance->claimAmount)->sum(),
                copayAmount: $aggregates->map(fn (Aggregate $x): int => $x->insurance->copayAmount)->sum(),
            ),
            subsidies: $this->buildSubsidies($userSubsidies, $aggregates),
            items: $items->toArray(),
            aggregates: $aggregates->toArray(),
            appendix: $this->buildAppendixUseCase->handle(
                context: $context,
                report: $report,
                insCardAtFirstOfMonth: $insCardAtFirstOfMonth,
                insCardAtLastOfMonth: $insCardAtLastOfMonth,
                office: $office,
                user: $user,
                serviceDetails: $details,
                serviceCodeMap: $this->getServiceCodeMap($context, $details, $bundle->providedIn),
            ),
            status: LtcsBillingStatus::ready(),
            fixedAt: null,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    /**
     * 契約を取得（特定）する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Office\Office $office
     * @param \Domain\User\User $user
     * @return \Domain\Contract\Contract
     */
    private function identifyContract(LtcsBillingBundle $bundle, Office $office, User $user): Contract
    {
        // 契約特定ユースケースは年月日を指定して特定することができない（2021-02-17 時点）ため自力で検索する.
        $filterParams = [
            'officeId' => $office->id,
            'userId' => $user->id,
            'serviceSegment' => ServiceSegment::longTermCare(),
            'status' => [ContractStatus::formal(), ContractStatus::terminated()],
            'contractedOnBefore' => $bundle->providedIn->endOfMonth(),
            'terminatedOnAfter' => $bundle->providedIn->startOfMonth(),
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
            'desc' => true,
        ];
        return $this->contractFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->headOption()
            ->getOrElse(function () use ($bundle, $office, $user): void {
                $month = $bundle->providedIn->format('Y-m');
                throw new NotFoundException("Contract for Office({$office->id}) and User({$user->id}) in {$month} not found");
            });
    }

    /**
     * （居宅介護支援事業所情報の組み立てるために）事業所を取得する.
     *
     * 請求データ生成のためにのみ用いるため権限を無視してリポジトリを直接操作する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCard
     * @param Context $context
     * @throws \Lib\Exceptions\LogicException
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\Office\Office
     */
    private function lookupOfficeForCarePlanAuthor(Context $context, LtcsInsCard $insCard): Office
    {
        $officeId = $insCard->carePlanAuthorOfficeId;
        if ($insCard->carePlanAuthorOfficeId === null) {
            throw new LogicException('Even though carePlanAuthorOfficeId is not null, the carePlanAuthorOfficeId is null');
        }
        return $this->officeRepository
            ->lookup($officeId)
            ->filter(fn (Office $x): bool => $x->organizationId === $context->organization->id)
            ->headOption()
            ->getOrElse(function () use ($officeId): void {
                throw new NotFoundException("Office({$officeId}) not found");
            });
    }

    /**
     * 介護保険サービス：明細書：明細を組み立てる.
     *
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $results
     * @param \Domain\User\UserLtcsSubsidy[][]&\ScalikePHP\Option[]&\ScalikePHP\Seq $userSubsidies
     * @return \Domain\Billing\LtcsBillingStatementItem[]&\ScalikePHP\Seq
     */
    private function buildItems(Seq $results, Seq $userSubsidies): Seq
    {
        return $results
            ->groupBy(fn (ServiceDetail $x): string => $x->serviceCode->toString() . ':' . $x->unitScore)
            ->mapValues(function (Seq $xs) use ($userSubsidies): Item {
                /** @var \Domain\Billing\LtcsBillingServiceDetail $head */
                $head = $xs->head();
                $unitScore = $head->unitScore;
                $count = $xs->size();
                $totalScore = $unitScore * $count;
                return new Item(
                    serviceCode: $head->serviceCode,
                    serviceCodeCategory: $head->serviceCodeCategory,
                    unitScore: $unitScore,
                    count: $count,
                    totalScore: $totalScore,
                    subsidies: $this->buildItemSubsidies($userSubsidies, $count, $totalScore)->toArray(),
                    note: $head->noteRequirement === LtcsNoteRequirement::durationMinutes()
                        ? (string)$head->durationMinutes
                        : '',
                );
            })
            ->values()
            ->computed();
    }

    /**
     * 介護保険サービス：明細書：明細：公費を組み立てる.
     *
     * @param \ScalikePHP\Seq $userSubsidies
     * @param int $count
     * @param int $totalScore
     * @return \Domain\Billing\LtcsBillingStatementItemSubsidy[]&\ScalikePHP\Seq
     */
    private function buildItemSubsidies(Seq $userSubsidies, int $count, int $totalScore): Seq
    {
        return $userSubsidies->map(function (Option $subsidyOption) use ($count, $totalScore): ItemSubsidy {
            return $subsidyOption
                ->map(fn (UserLtcsSubsidy $subsidy): ItemSubsidy => new ItemSubsidy(
                    count: $count,
                    totalScore: Math::round($totalScore * $subsidy->benefitRate / 100),
                ))
                ->getOrElseValue(ItemSubsidy::empty());
        });
    }

    /**
     * 介護保険サービス：明細書：集計を組み立てる.
     *
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param \Domain\User\UserLtcsSubsidy[][]&\ScalikePHP\Option[]&\ScalikePHP\Seq $userSubsidies
     * @param \Domain\Common\Decimal $unitCost
     * @param int $benefitRate
     * @param int $excessScore
     * @return \Domain\Billing\LtcsBillingStatementAggregate[]&\ScalikePHP\Seq
     */
    private function buildAggregates(
        Seq $details,
        Seq $userSubsidies,
        Decimal $unitCost,
        int $benefitRate,
        int $excessScore,
    ): Seq {
        return $details
            ->groupBy(fn (ServiceDetail $x): string => $x->serviceCode->serviceDivisionCode)
            ->mapValues(fn (Seq $xs, $serviceDivisionCode): Aggregate => $this->buildAggregate(
                $userSubsidies,
                $unitCost,
                $benefitRate,
                LtcsServiceDivisionCode::from((string)$serviceDivisionCode),
                $xs,
                $excessScore
            ))
            ->values()
            ->computed();
    }

    /**
     * 介護保険サービス：明細書：集計を組み立てる.
     *
     * @param \Domain\User\UserLtcsSubsidy[][]&\ScalikePHP\Option[]&\ScalikePHP\Seq $userSubsidies
     * @param \Domain\Common\Decimal $unitCost
     * @param int $benefitRate
     * @param \Domain\Billing\LtcsServiceDivisionCode $serviceDivisionCode
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param int $excessScore
     * @return \Domain\Billing\LtcsBillingStatementAggregate
     */
    private function buildAggregate(
        Seq $userSubsidies,
        Decimal $unitCost,
        int $benefitRate,
        LtcsServiceDivisionCode $serviceDivisionCode,
        Seq $details,
        int $excessScore,
    ): Aggregate {
        [$managedScore, $unmanagedScore] = ServiceDetail::aggregateScore(
            details: $details,
            excessScore: $excessScore
        );
        $serviceDays = $details
            ->filter(fn (ServiceDetail $x): bool => !$x->isAddition)
            ->map(fn (ServiceDetail $x): string => $x->providedOn->toDateString())
            ->distinct()
            ->size();
        return Aggregate::from(
            userSubsidies: $userSubsidies,
            benefitRate: $benefitRate,
            serviceDivisionCode: $serviceDivisionCode,
            serviceDays: $serviceDays,
            plannedScore: $managedScore, // 生成時は「④計画単位数」に「⑤限度額管理対象単位数」と同じ値を用いる
            managedScore: $managedScore,
            unmanagedScore: $unmanagedScore,
            unitCost: $unitCost
        );
    }

    /**
     * 介護保険サービス：明細書：公費請求内容を組み立てる.
     *
     * @param \Domain\User\UserLtcsSubsidy[][]&\ScalikePHP\Option[]&\ScalikePHP\Seq $userSubsidies
     * @param \Domain\Billing\LtcsBillingStatementAggregate[]&\ScalikePHP\Seq $aggregates
     * @return array&\Domain\Billing\LtcsBillingStatementSubsidy[]
     */
    private function buildSubsidies(Seq $userSubsidies, Seq $aggregates): array
    {
        assert($userSubsidies->size() === self::MAX_SUBSIDIES);
        return $userSubsidies
            ->map(function (Option $subsidyOption) use ($aggregates): Subsidy {
                return $subsidyOption
                    ->map(function (UserLtcsSubsidy $subsidy) use ($aggregates): Subsidy {
                        $xs = $aggregates->map(fn (Aggregate $x): AggregateSubsidy => $x->subsidies[0]);
                        return new Subsidy(
                            defrayerCategory: $subsidy->defrayerCategory,
                            defrayerNumber: $subsidy->defrayerNumber,
                            recipientNumber: $subsidy->recipientNumber,
                            benefitRate: $subsidy->benefitRate,
                            totalScore: $xs->map(fn (AggregateSubsidy $x): int => $x->totalScore)->sum(),
                            claimAmount: $xs->map(fn (AggregateSubsidy $x): int => $x->claimAmount)->sum(),
                            copayAmount: $xs->map(fn (AggregateSubsidy $x): int => $x->copayAmount)->sum(),
                        );
                    })
                    ->getOrElseValue(Subsidy::empty());
            })
            ->toArray();
    }

    /**
     * 介護保険サービス：請求：居宅サービス計画を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCard
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\Billing\LtcsCarePlanAuthor
     */
    private function buildCarePlanAuthor(Context $context, LtcsInsCard $insCard): LtcsCarePlanAuthor
    {
        $authorType = $insCard->carePlanAuthorType;
        switch ($authorType) {
            case LtcsCarePlanAuthorType::careManagerOffice():
                $office = $this->lookupOfficeForCarePlanAuthor($context, $insCard);
                return new LtcsCarePlanAuthor(
                    authorType: $authorType,
                    officeId: $office->id,
                    code: $office->ltcsCareManagementService->code !== null ? $office->ltcsCareManagementService->code : $office->ltcsHomeVisitLongTermCareService->code,
                    name: $office->name,
                );
            default:
                return new LtcsCarePlanAuthor(
                    authorType: $authorType,
                    officeId: null,
                    code: '',
                    name: '',
                );
        }
    }

    /**
     * サービスコード => サービス名称 の Map を生成する.
     *
     * @param Context $context
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails
     * @param \Domain\Common\Carbon $providedIn
     * @return \ScalikePHP\Map&string[]
     */
    private function getServiceCodeMap(Context $context, Seq $serviceDetails, Carbon $providedIn): Map
    {
        $serviceCodes = $serviceDetails
            ->map(fn (LtcsBillingServiceDetail $x): ServiceCode => $x->serviceCode)
            ->distinctBy(fn (ServiceCode $x): string => $x->toString());
        return $this->resolveLtcsNameFromServiceCodesUseCase->handle($context, $serviceCodes->computed(), $providedIn);
    }
}
