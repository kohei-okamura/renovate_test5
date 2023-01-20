<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus as CopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementElement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractFinder;
use Domain\Contract\ContractStatus;
use Domain\DwsAreaGrade\DwsAreaGrade;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\Office;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\User\User;
use Domain\User\UserDwsSubsidy;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\Office\LookupDwsAreaGradeUseCase;
use UseCase\User\IdentifyUserDwsSubsidyUseCase;

/**
 * 障害福祉サービス：明細書組み立てユースケース実装.
 */
final class BuildDwsBillingStatementInteractor implements BuildDwsBillingStatementUseCase
{
    use DwsBillingStatementAggregateAggregator;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementAggregateListUseCase $buildAggregateListUseCase
     * @param \UseCase\Billing\BuildDwsBillingStatementContractListUseCase $buildContractListUseCase
     * @param \UseCase\Billing\BuildDwsBillingStatementElementListUseCase $buildElementListUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     * @param \UseCase\User\IdentifyUserDwsSubsidyUseCase $identifyUserDwsSubsidyUseCase
     * @param \UseCase\Office\LookupDwsAreaGradeUseCase $lookupDwsAreaGradeUseCase
     * @param \Domain\Contract\ContractFinder $contractFinder
     */
    public function __construct(
        private readonly BuildDwsBillingStatementAggregateListUseCase $buildAggregateListUseCase,
        private readonly BuildDwsBillingStatementContractListUseCase $buildContractListUseCase,
        private readonly BuildDwsBillingStatementElementListUseCase $buildElementListUseCase,
        private readonly IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
        private readonly IdentifyUserDwsSubsidyUseCase $identifyUserDwsSubsidyUseCase,
        private readonly LookupDwsAreaGradeUseCase $lookupDwsAreaGradeUseCase,
        private readonly ContractFinder $contractFinder
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Office $office,
        DwsBillingBundle $bundle,
        ?HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        ?VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec,
        User $user,
        Seq $details,
        Option $copayCoordinationOption,
        Option $baseStatementOption
    ): DwsBillingStatement {
        $certification = $this->identifyDwsCertification($context, $bundle, $user);
        $userSubsidyOption = $this->identifySubsidy($context, $bundle, $user);
        $dwsAreaGrade = $this->lookupDwsAreaGrade($context, $office);
        $elements = $this->buildElementList(
            $context,
            $office,
            $bundle,
            $homeHelpServiceCalcSpec,
            $visitingCareForPwsdCalcSpec,
            $details,
            $copayCoordinationOption,
            $certification
        );
        $aggregates = $this->buildAggregateListUseCase->handle(
            $context,
            $office,
            $bundle->providedIn,
            $this->identifyContract($bundle, $office, $user),
            $certification,
            $userSubsidyOption,
            $elements,
            self::computeCopayCoordinationAmount($baseStatementOption),
            $baseStatementOption
        );
        $contracts = $this->buildContractListUseCase->handle($context, $office, $certification, $bundle->providedIn);

        $subsidyCityCode = $userSubsidyOption->map(fn (UserDwsSubsidy $x): string => $x->cityCode)->getOrElseValue('');
        $copayCoordinationStatus = self::computeCopayCoordinationStatus($baseStatementOption, $office, $certification);
        return DwsBillingStatement::create([
            'id' => $baseStatementOption->map(fn (DwsBillingStatement $x): int => $x->id)->orNull(),
            'dwsBillingId' => $bundle->dwsBillingId,
            'dwsBillingBundleId' => $bundle->id,
            'subsidyCityCode' => $subsidyCityCode,
            'user' => DwsBillingUser::from($user, $certification),
            'dwsAreaGradeName' => $dwsAreaGrade->name,
            'dwsAreaGradeCode' => $dwsAreaGrade->code,
            'copayLimit' => $certification->copayLimit,
            'totalScore' => $this->aggregateSubtotalScore($aggregates),
            'totalFee' => $this->aggregateSubtotalFee($aggregates),
            'totalCappedCopay' => $this->aggregateCappedCopay($aggregates),
            'totalAdjustedCopay' => $this->aggregateAdjustedCopay($aggregates),
            'totalCoordinatedCopay' => $this->aggregateCoordinatedCopay($aggregates),
            'totalCopay' => $this->aggregateSubtotalCopay($aggregates),
            'totalBenefit' => $this->aggregateSubtotalBenefit($aggregates),
            'totalSubsidy' => $this->aggregateSubtotalSubsidy($aggregates),
            'isProvided' => $details->nonEmpty(),
            'copayCoordination' => $baseStatementOption->pick('copayCoordination')->orNull(),
            'copayCoordinationStatus' => $copayCoordinationStatus,
            'aggregates' => [...$aggregates],
            'contracts' => [...$contracts],
            'items' => $elements
                ->map(fn (DwsBillingStatementElement $x): DwsBillingStatementItem => $x->toItem())
                ->toArray(),
            'status' => self::computeStatus($baseStatementOption, $copayCoordinationStatus),
            'fixedAt' => $baseStatementOption->pick('fixedAt')->orNull(),
            'createdAt' => $baseStatementOption->pick('createdAt')->orNull() ?? Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * 障害福祉サービス：明細書：要素の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param null|\Domain\Office\HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec
     * @param null|\Domain\Office\VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
     * @param \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Option $copayCoordinationOption 上限管理結果票
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq
     */
    private function buildElementList(
        Context $context,
        Office $office,
        DwsBillingBundle $bundle,
        ?HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        ?VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec,
        Seq $details,
        Option $copayCoordinationOption,
        DwsCertification $certification,
    ): Seq {
        // 以下の条件の場合は true (加算を算定する）
        // - 上限管理事業所である
        // - 上限管理結果票が確定済みではない もしくは 確定済みで他事業所のサービスがある場合
        $isCopayCoordinationAdditionEnabled =
            self::isSelfCoordination($certification, $office)
            && $copayCoordinationOption->forAll(
                function (DwsBillingCopayCoordination $x): bool {
                    return $x->status !== DwsBillingStatus::fixed()
                        || (count($x->items) > 1 && $x->status === DwsBillingStatus::fixed());
                }
            );
        return $this->buildElementListUseCase->handle(
            $context,
            $homeHelpServiceCalcSpec,
            $visitingCareForPwsdCalcSpec,
            $isCopayCoordinationAdditionEnabled,
            $bundle->providedIn,
            $details
        );
    }

    /**
     * 障害福祉契約を取得（特定）する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Office\Office $office
     * @param \Domain\User\User $user
     * @return \Domain\Contract\Contract
     */
    private function identifyContract(DwsBillingBundle $bundle, Office $office, User $user): Contract
    {
        // 契約特定ユースケースは年月日を指定して特定することができない（2021-02-17 時点）ため自力で検索する.
        $filterParams = [
            'officeId' => $office->id,
            'userId' => $user->id,
            'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
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
     * 障害福祉サービス受給者証を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyDwsCertification(Context $context, DwsBillingBundle $bundle, User $user): DwsCertification
    {
        $targetDate = $bundle->providedIn;
        return $this->identifyDwsCertificationUseCase
            ->handle($context, $user->id, $targetDate)
            ->getOrElse(function () use ($user, $targetDate): void {
                throw new NotFoundException("DwsCertification for User({$user->id}) in {$targetDate} not found");
            });
    }

    /**
     * 利用者：自治体助成情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @return \Domain\User\UserDwsSubsidy[]&\ScalikePHP\Option
     */
    private function identifySubsidy(Context $context, DwsBillingBundle $bundle, User $user): Option
    {
        return $this->identifyUserDwsSubsidyUseCase->handle($context, $user, $bundle->providedIn);
    }

    /**
     * 地域区分を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @return \Domain\DwsAreaGrade\DwsAreaGrade
     */
    private function lookupDwsAreaGrade(Context $context, Office $office): DwsAreaGrade
    {
        $id = $office->dwsGenericService->dwsAreaGradeId;
        return $this->lookupDwsAreaGradeUseCase
            ->handle($context, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsAreaGrade({$id}) is not found");
            });
    }

    /**
     * 《上限管理区分》を算出する.
     *
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $baseStatementOption
     * @param \Domain\Office\Office $office
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @return \Domain\Billing\DwsBillingStatementCopayCoordinationStatus
     */
    private static function computeCopayCoordinationStatus(
        Option $baseStatementOption,
        Office $office,
        DwsCertification $certification
    ): CopayCoordinationStatus {
        $statusOption = $baseStatementOption->pick('copayCoordinationStatus');
        return $statusOption->getOrElse(function () use ($office, $certification): CopayCoordinationStatus {
            return match ($certification->copayCoordination->copayCoordinationType) {
                CopayCoordinationType::internal() => $certification->copayCoordination->officeId === $office->id
                    ? CopayCoordinationStatus::uncreated()
                    : CopayCoordinationStatus::unfilled(),
                CopayCoordinationType::external() => CopayCoordinationStatus::unfilled(),
                default => CopayCoordinationStatus::unapplicable(),
            };
        });
    }

    /**
     * 上限管理結果額を計算する.
     *
     * @param \Domain\Billing\DwsBillingStatement&\ScalikePHP\Option $baseStatementOption
     * @return int[]&\ScalikePHP\Option
     */
    private static function computeCopayCoordinationAmount(Option $baseStatementOption): Option
    {
        return $baseStatementOption
            ->flatMap(fn (DwsBillingStatement $x): Option => Option::from($x->copayCoordination))
            ->map(fn (DwsBillingStatementCopayCoordination $x): int => $x->amount);
    }

    /**
     * 状態を決定する.
     *
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $baseStatementOption
     * @param \Domain\Billing\DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus
     * @return \Domain\Billing\DwsBillingStatus
     */
    private static function computeStatus(
        Option $baseStatementOption,
        CopayCoordinationStatus $copayCoordinationStatus
    ): DwsBillingStatus {
        $baseStatus = $baseStatementOption->pick('status')->orNull();
        return match ($baseStatus) {
            DwsBillingStatus::fixed() => $baseStatus,
            default => $copayCoordinationStatus->isCompleted()
                ? DwsBillingStatus::ready()
                : DwsBillingStatus::checking()
        };
    }

    /**
     * 上限管理が自事業所であるか判定する.
     *
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Office\Office $office
     * @return bool
     */
    private static function isSelfCoordination(DwsCertification $certification, Office $office): bool
    {
        return $certification->copayCoordination->copayCoordinationType === CopayCoordinationType::internal()
            && $certification->copayCoordination->officeId === $office->id;
    }
}
