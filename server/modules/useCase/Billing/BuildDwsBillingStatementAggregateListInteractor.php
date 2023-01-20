<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementElement as Element;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\DwsAreaGrade\DwsAreaGradeFee;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase;

/**
 * 障害福祉サービス：明細書：集計一覧組み立てユースケース実装.
 */
final class BuildDwsBillingStatementAggregateListInteractor implements BuildDwsBillingStatementAggregateListUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementAggregateListInteractor} constructor.
     *
     * @param \UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase $identifyAreaGradeFeeUseCase
     */
    public function __construct(
        private readonly IdentifyDwsAreaGradeFeeUseCase $identifyAreaGradeFeeUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Office $office,
        Carbon $providedIn,
        Contract $contract,
        DwsCertification $certification,
        Option $userSubsidyOption,
        Seq $elements,
        Option $coordinatedCopayOption,
        Option $baseStatementOption
    ): Seq {
        $groupedElements = $elements->groupBy(fn (Element $x): string => $x->serviceCode->serviceDivisionCode)->toSeq();
        $aggregatesCount = $groupedElements->size();
        $unitCost = $this->identifyUnitCost($context, $office, $providedIn);
        return $this->generateRecursive(
            $context,
            $office,
            $providedIn,
            $contract,
            $certification,
            $userSubsidyOption,
            $groupedElements,
            $aggregatesCount,
            $unitCost,
            $coordinatedCopayOption,
            $baseStatementOption
        );
    }

    /**
     * 障害福祉サービス：明細書：集計の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Contract\Contract $contract
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \ScalikePHP\Option $userSubsidyOption
     * @param \ScalikePHP\Seq $groupedElements
     * @param int $aggregatesCount
     * @param \Domain\Common\Decimal $unitCost
     * @param int[]&\ScalikePHP\Option $coordinatedCopayOption 上限管理結果額
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $baseStatementOption
     * @param int $consumedAdjustedCopay
     * @return \Domain\Billing\DwsBillingStatementAggregate[]&\ScalikePHP\Seq
     */
    private function generateRecursive(
        Context $context,
        Office $office,
        Carbon $providedIn,
        Contract $contract,
        DwsCertification $certification,
        Option $userSubsidyOption,
        Seq $groupedElements,
        int $aggregatesCount,
        Decimal $unitCost,
        Option $coordinatedCopayOption,
        Option $baseStatementOption,
        int $consumedAdjustedCopay = 0
    ): Seq {
        if ($groupedElements->isEmpty()) {
            return Seq::empty();
        } else {
            [$serviceDivisionCodeValue, $elements] = $groupedElements->head();
            $baseAggregateOption = $baseStatementOption->flatMap(
                fn (DwsBillingStatement $statement): Option => Seq::fromArray($statement->aggregates)->find(
                    function (DwsBillingStatementAggregate $x) use ($serviceDivisionCodeValue): bool {
                        return (int)$x->serviceDivisionCode->value() === $serviceDivisionCodeValue;
                    }
                )
            );
            $aggregate = DwsBillingStatementAggregate::from(
                $contract,
                $certification,
                $userSubsidyOption,
                DwsServiceDivisionCode::from((string)$serviceDivisionCodeValue),
                $aggregatesCount,
                $unitCost,
                self::computeServiceDays($elements, $providedIn),
                self::computeSubtotalScore($elements),
                $consumedAdjustedCopay,
                $baseAggregateOption->map(fn (DwsBillingStatementAggregate $x): int => $x->managedCopay),
                $coordinatedCopayOption,
                $baseAggregateOption->flatMap(
                    fn (DwsBillingStatementAggregate $x): Option => Option::from($x->subtotalSubsidy)
                ),
            );
            return Seq::from(
                $aggregate,
                ...$this->generateRecursive(
                    $context,
                    $office,
                    $providedIn,
                    $contract,
                    $certification,
                    $userSubsidyOption,
                    $groupedElements->drop(1),
                    $aggregatesCount,
                    $unitCost,
                    $coordinatedCopayOption->map(fn (int $x): int => $x - $aggregate->coordinatedCopay),
                    $baseStatementOption,
                    $consumedAdjustedCopay + ($aggregate->adjustedCopay ?? 0),
                )
            );
        }
    }

    /**
     * 単位数単価を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Common\Decimal
     */
    private function identifyUnitCost(Context $context, Office $office, Carbon $providedIn): Decimal
    {
        $dwsAreaGradeId = $office->dwsGenericService->dwsAreaGradeId;
        return $this->identifyAreaGradeFeeUseCase
            ->handle($context, $dwsAreaGradeId, $providedIn)
            ->map(fn (DwsAreaGradeFee $x): Decimal => $x->fee)
            ->getOrElse(function () use ($dwsAreaGradeId, $providedIn): void {
                $date = $providedIn->toDateString();
                throw new SetupException("DwsAreaGradeFee({$dwsAreaGradeId}/{$date}) not found");
            });
    }

    /**
     * 《サービス利用日数》を算出する.
     *
     * 加算を除いた本体サービスを対象に算出する.
     * ただし加算のみの場合は 0 とせずに 1 を算出する.
     *
     * @param \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq $elements
     * @param \Domain\Common\Carbon $providedIn
     * @return int
     */
    private static function computeServiceDays(Seq $elements, Carbon $providedIn): int
    {
        $count = $elements
            ->filterNot(fn (Element $x): bool => $x->isAddition)
            ->flatMap(fn (Element $x): iterable => $x->providedOn)
            ->filter(fn (Carbon $x): bool => $x->isSameMonth($providedIn))
            ->map(fn (Carbon $x): string => $x->toDateString())
            ->distinct()
            ->size();
        return $count === 0 ? 1 : $count;
    }

    /**
     * 《給付単位数》を算出する.
     *
     * @param \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq $elements
     * @return int
     */
    private static function computeSubtotalScore(Seq $elements): int
    {
        return $elements->map(fn (Element $x): int => $x->unitScore * $x->count)->sum();
    }
}
