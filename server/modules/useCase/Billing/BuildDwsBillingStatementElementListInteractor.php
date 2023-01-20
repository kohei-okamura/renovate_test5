<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingStatementElement as Element;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Lib\Exceptions\LogicException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\ScalikeTraversable;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書：要素一覧組み立てユースケース実装.
 */
final class BuildDwsBillingStatementElementListInteractor implements BuildDwsBillingStatementElementListUseCase
{
    private DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder;
    private DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementElementListInteractor} constructor.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder
     */
    public function __construct(
        DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder,
        DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder
    ) {
        $this->homeHelpServiceDictionaryEntryFinder = $homeHelpServiceDictionaryEntryFinder;
        $this->visitingCareForPwsdDictionaryEntryFinder = $visitingCareForPwsdDictionaryEntryFinder;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        ?HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        ?VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec,
        bool $enableCopayCoordinationAddition,
        Carbon $providedIn,
        Seq $details
    ): Seq {
        $elements = $this->generateElements($details);
        $getElementsByServiceDivisionCode = fn (DwsServiceDivisionCode $code): Seq => $elements
            ->get($code->value())
            ->toSeq()
            ->flatten();
        $homeHelpServiceElements = $getElementsByServiceDivisionCode(DwsServiceDivisionCode::homeHelpService());
        $visitingCareForPwsdElements = $getElementsByServiceDivisionCode(DwsServiceDivisionCode::visitingCareForPwsd());

        $isHomeHelpServiceProvided = $homeHelpServiceElements->nonEmpty();
        $isVisitingCareForPwsdProvided = $visitingCareForPwsdElements->nonEmpty();

        $homeHelpServiceCopayCoordinationAddition = $this->generateHomeHelpServiceCopayCoordinationAddition(
            $enableCopayCoordinationAddition,
            $isHomeHelpServiceProvided,
            $isVisitingCareForPwsdProvided,
            $providedIn
        );
        $visitingCareForPwsdCopayCoordinationAddition = $this->generateVisitingCareForPwsdCopayCoordinationAddition(
            $enableCopayCoordinationAddition,
            $isVisitingCareForPwsdProvided,
            $providedIn
        );

        $homeHelpServiceBaseScore = $this->computeSum($homeHelpServiceElements)
            + $this->computeSum($homeHelpServiceCopayCoordinationAddition);
        $visitingCareForPwsdBaseScore = $this->computeSum($visitingCareForPwsdElements)
            + $this->computeSum($visitingCareForPwsdCopayCoordinationAddition);

        return Seq::from(
            ...$homeHelpServiceElements,
            ...$homeHelpServiceCopayCoordinationAddition,
            ...$this->generateHomeHelpServiceTreatmentImprovementAddition(
                $homeHelpServiceCalcSpec,
                $providedIn,
                $homeHelpServiceBaseScore
            ),
            ...$this->generateHomeHelpServiceSpecifiedTreatmentImprovementAddition(
                $homeHelpServiceCalcSpec,
                $providedIn,
                $homeHelpServiceBaseScore
            ),
            ...$this->generateHomeHelpServiceBaseIncreaseSupportAddition(
                $homeHelpServiceCalcSpec,
                $providedIn,
                $homeHelpServiceBaseScore
            ),
            ...$visitingCareForPwsdElements,
            ...$visitingCareForPwsdCopayCoordinationAddition,
            ...$this->generateVisitingCareForPwsdTreatmentImprovementAddition(
                $visitingCareForPwsdCalcSpec,
                $providedIn,
                $visitingCareForPwsdBaseScore
            ),
            ...$this->generateVisitingCareForPwsdSpecifiedTreatmentImprovementAddition(
                $visitingCareForPwsdCalcSpec,
                $providedIn,
                $visitingCareForPwsdBaseScore
            ),
            ...$this->generateVisitingCareForPwsdBaseIncreaseSupportAddition(
                $visitingCareForPwsdCalcSpec,
                $providedIn,
                $visitingCareForPwsdBaseScore
            ),
        );
    }

    /**
     * 要素の一覧から合計単位数を算出する.
     *
     * @param \ScalikePHP\ScalikeTraversable $items
     * @return int
     */
    private function computeSum(ScalikeTraversable $items): int
    {
        return $items->map(fn (Element $x): int => $x->unitScore * $x->count)->sum();
    }

    /**
     * サービス詳細の一覧から要素の一覧を生成する.
     *
     * @param \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq $details
     * @return \Domain\Billing\DwsBillingStatementElement[][]|\ScalikePHP\Map|\ScalikePHP\Seq[]
     */
    private function generateElements(Seq $details): Map
    {
        return $details
            ->groupBy(fn (DwsBillingServiceDetail $x): string => $x->serviceCode->toString())
            // PHP の仕様でサービスコード（文字列）が数値になるので型指定を省略する
            ->map(function (Seq $xs, $serviceCodeValue): array {
                $serviceCodeString = (string)$serviceCodeValue;
                $providedOn = $xs
                    ->map(fn (DwsBillingServiceDetail $x): Carbon => $x->providedOn)
                    ->distinctBy(fn (Carbon $x): string => $x->toDateString());
                $serviceCodeCategory = $xs
                    ->headOption()
                    ->map(fn (DwsBillingServiceDetail $x): DwsServiceCodeCategory => $x->serviceCodeCategory)
                    ->getOrElse(function (): void {
                        throw new LogicException('DwsBillingServiceDetails cannot be empty');
                    });
                $element = Element::create([
                    'serviceCode' => ServiceCode::fromString($serviceCodeString),
                    'serviceCodeCategory' => $serviceCodeCategory,
                    'unitScore' => $xs->head()->unitScore,
                    'isAddition' => $xs->head()->isAddition,
                    'count' => $xs->map(fn (DwsBillingServiceDetail $x): int => $x->count)->sum(),
                    'providedOn' => [...$providedOn],
                ]);
                return [$serviceCodeValue, $element];
            })
            ->values()
            ->groupBy(fn (Element $x): string => $x->serviceCode->serviceDivisionCode);
    }

    /**
     * 加算を表す要素を生成する.
     *
     * @param \Domain\ServiceCode\ServiceCode $serviceCode
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $serviceCodeCategory
     * @param int $score
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\DwsBillingStatementElement
     */
    private function generateAddition(ServiceCode $serviceCode, DwsServiceCodeCategory $serviceCodeCategory, int $score, Carbon $providedIn): Element
    {
        return Element::create([
            'serviceCode' => $serviceCode,
            'serviceCodeCategory' => $serviceCodeCategory,
            'unitScore' => $score,
            'isAddition' => true,
            'count' => 1,
            'providedOn' => [$providedIn->endOfMonth()->startOfDay()],
        ]);
    }

    /**
     * 利用者負担上限額管理加算（居宅介護）の要素を生成する.
     *
     * 重度訪問介護のサービス提供がある場合は居宅介護の利用者負担上限額管理加算は算定しない.
     *
     * @param bool $enableCopayCoordinationAddition
     * @param bool $isHomeHelpServiceProvided
     * @param bool $isVisitingCareForPwsdProvided
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Option
     */
    private function generateHomeHelpServiceCopayCoordinationAddition(
        bool $enableCopayCoordinationAddition,
        bool $isHomeHelpServiceProvided,
        bool $isVisitingCareForPwsdProvided,
        Carbon $providedIn
    ): Option {
        if ($enableCopayCoordinationAddition && $isHomeHelpServiceProvided && !$isVisitingCareForPwsdProvided) {
            $entry = $this->homeHelpServiceDictionaryEntryFinder->findByCategory(
                $providedIn,
                DwsServiceCodeCategory::copayCoordinationAddition()
            );
            $element = $this->generateAddition($entry->serviceCode, $entry->category, $entry->score, $providedIn);
            return Option::some($element);
        } else {
            return Option::none();
        }
    }

    /**
     * 利用者負担上限額管理加算（重度訪問介護）の要素を生成する.
     *
     * @param bool $enableCopayCoordinationAddition
     * @param bool $isVisitingCareForPwsdProvided
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Option
     */
    private function generateVisitingCareForPwsdCopayCoordinationAddition(
        bool $enableCopayCoordinationAddition,
        bool $isVisitingCareForPwsdProvided,
        Carbon $providedIn
    ): Option {
        if ($enableCopayCoordinationAddition && $isVisitingCareForPwsdProvided) {
            $entry = $this->visitingCareForPwsdDictionaryEntryFinder->findByCategory(
                $providedIn,
                DwsServiceCodeCategory::copayCoordinationAddition()
            );
            $element = $this->generateAddition($entry->serviceCode, $entry->category, $entry->score, $providedIn);
            return Option::some($element);
        } else {
            return Option::none();
        }
    }

    /**
     * 福祉・介護職員処遇改善加算（居宅介護）の要素を生成する.
     *
     * @param \Domain\Office\HomeHelpServiceCalcSpec $spec
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary $dictionary
     * @param \Domain\Common\Carbon $providedIn
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingStatementElement[]|\ScalikePHP\Option
     */
    private function generateHomeHelpServiceTreatmentImprovementAddition(
        ?HomeHelpServiceCalcSpec $spec,
        Carbon $providedIn,
        int $baseScore
    ): Option {
        if ($baseScore === 0 || $spec === null) {
            return Option::none();
        }
        $categoryOption = DwsServiceCodeCategory::fromTreatmentImprovementAddition($spec->treatmentImprovementAddition);
        return $categoryOption->map(
            function (DwsServiceCodeCategory $category) use ($spec, $baseScore, $providedIn): Element {
                $entry = $this->homeHelpServiceDictionaryEntryFinder->findByCategory($providedIn, $category);
                $score = $spec->treatmentImprovementAddition
                    ->compute($baseScore, DwsServiceDivisionCode::homeHelpService(), $providedIn)
                    ->getOrElse(function () use ($category): void {
                        throw new LogicException("Failed to compute score for DwsTreatmentImprovementAddition({$category})");
                    });
                return $this->generateAddition($entry->serviceCode, $entry->category, $score, $providedIn);
            }
        );
    }

    /**
     * 福祉・介護職員処遇改善加算（重度訪問介護）の要素を生成する.
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $spec
     * @param \Domain\Common\Carbon $providedIn
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingStatementElement[]|\ScalikePHP\Option
     */
    private function generateVisitingCareForPwsdTreatmentImprovementAddition(
        ?VisitingCareForPwsdCalcSpec $spec,
        Carbon $providedIn,
        int $baseScore
    ): Option {
        if ($baseScore === 0 || $spec === null) {
            return Option::none();
        }
        $categoryOption = DwsServiceCodeCategory::fromTreatmentImprovementAddition($spec->treatmentImprovementAddition);
        return $categoryOption->map(
            function (DwsServiceCodeCategory $category) use ($spec, $baseScore, $providedIn): Element {
                $entry = $this->visitingCareForPwsdDictionaryEntryFinder->findByCategory($providedIn, $category);
                $score = $spec->treatmentImprovementAddition
                    ->compute($baseScore, DwsServiceDivisionCode::visitingCareForPwsd(), $providedIn)
                    ->getOrElse(function () use ($category): void {
                        throw new LogicException("Failed to compute score for DwsTreatmentImprovementAddition({$category})");
                    });
                return $this->generateAddition($entry->serviceCode, $entry->category, $score, $providedIn);
            }
        );
    }

    /**
     * 福祉・介護職員特定処遇改善加算（居宅介護）の要素を生成する.
     *
     * @param \Domain\Office\HomeHelpServiceCalcSpec $spec
     * @param \Domain\Common\Carbon $providedIn
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Option
     */
    private function generateHomeHelpServiceSpecifiedTreatmentImprovementAddition(
        ?HomeHelpServiceCalcSpec $spec,
        Carbon $providedIn,
        int $baseScore
    ): Option {
        if ($baseScore === 0 || $spec === null) {
            return Option::none();
        }
        $categoryOption = DwsServiceCodeCategory::fromSpecifiedTreatmentImprovementAddition(
            $spec->specifiedTreatmentImprovementAddition
        );
        return $categoryOption->map(
            function (DwsServiceCodeCategory $category) use ($spec, $baseScore, $providedIn): Element {
                $entry = $this->homeHelpServiceDictionaryEntryFinder->findByCategory($providedIn, $category);
                $score = $spec->specifiedTreatmentImprovementAddition
                    ->compute($baseScore, DwsServiceDivisionCode::homeHelpService(), $providedIn)
                    ->getOrElse(function () use ($category): void {
                        throw new LogicException("Failed to compute score for DwsSpecifiedTreatmentImprovementAddition({$category})");
                    });
                return $this->generateAddition($entry->serviceCode, $entry->category, $score, $providedIn);
            }
        );
    }

    /**
     * 福祉・介護職員特定処遇改善加算（重度訪問介護）の要素を生成する.
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $spec
     * @param \Domain\Common\Carbon $providedIn
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Option
     */
    private function generateVisitingCareForPwsdSpecifiedTreatmentImprovementAddition(
        ?VisitingCareForPwsdCalcSpec $spec,
        Carbon $providedIn,
        int $baseScore
    ): Option {
        if ($baseScore === 0 || $spec === null) {
            return Option::none();
        }
        $categoryOption = DwsServiceCodeCategory::fromSpecifiedTreatmentImprovementAddition(
            $spec->specifiedTreatmentImprovementAddition
        );
        return $categoryOption->map(
            function (DwsServiceCodeCategory $category) use ($spec, $baseScore, $providedIn): Element {
                $entry = $this->visitingCareForPwsdDictionaryEntryFinder->findByCategory($providedIn, $category);
                $score = $spec->specifiedTreatmentImprovementAddition
                    ->compute($baseScore, DwsServiceDivisionCode::visitingCareForPwsd(), $providedIn)
                    ->getOrElse(function () use ($category): void {
                        throw new LogicException("Failed to compute score for DwsTreatmentImprovementAddition({$category})");
                    });
                return $this->generateAddition($entry->serviceCode, $entry->category, $score, $providedIn);
            }
        );
    }

    /**
     * 福祉・介護職員等ベースアップ等支援加算（居宅介護）の要素を生成する.
     *
     * @param null|\Domain\Office\VisitingCareForPwsdCalcSpec $spec
     * @param \Domain\Common\Carbon $providedIn
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Option
     */
    private function generateHomeHelpServiceBaseIncreaseSupportAddition(
        ?HomeHelpServiceCalcSpec $spec,
        Carbon $providedIn,
        int $baseScore
    ): Option {
        if ($baseScore === 0 || $spec === null) {
            return Option::none();
        }
        if ($spec->baseIncreaseSupportAddition === DwsBaseIncreaseSupportAddition::addition1()) {
            $category = DwsServiceCodeCategory::baseIncreaseSupportAddition();
            $entry = $this->homeHelpServiceDictionaryEntryFinder->findByCategory($providedIn, $category);
            $score = $spec->baseIncreaseSupportAddition
                ->compute($baseScore, DwsServiceDivisionCode::homeHelpService(), $providedIn)
                ->getOrElse(function () use ($category): void {
                    throw new LogicException("Failed to compute score for DwsBaseIncreaseSupportAddition({$category})");
                });
            return Option::from($this->generateAddition($entry->serviceCode, $entry->category, $score, $providedIn));
        } else {
            return Option::none();
        }
    }

    /**
     * 福祉・介護職員等ベースアップ等支援加算（重度訪問介護）の要素を生成する.
     *
     * @param null|\Domain\Office\VisitingCareForPwsdCalcSpec $spec
     * @param \Domain\Common\Carbon $providedIn
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Option
     */
    private function generateVisitingCareForPwsdBaseIncreaseSupportAddition(
        ?VisitingCareForPwsdCalcSpec $spec,
        Carbon $providedIn,
        int $baseScore
    ): Option {
        if ($baseScore === 0 || $spec === null) {
            return Option::none();
        }
        if ($spec->baseIncreaseSupportAddition === DwsBaseIncreaseSupportAddition::addition1()) {
            $category = DwsServiceCodeCategory::baseIncreaseSupportAddition();
            $entry = $this->visitingCareForPwsdDictionaryEntryFinder->findByCategory($providedIn, $category);
            $score = $spec->baseIncreaseSupportAddition
                ->compute($baseScore, DwsServiceDivisionCode::visitingCareForPwsd(), $providedIn)
                ->getOrElse(function () use ($category): void {
                    throw new LogicException("Failed to compute score for DwsBaseIncreaseSupportAddition({$category})");
                });
            return Option::from($this->generateAddition($entry->serviceCode, $entry->category, $score, $providedIn));
        } else {
            return Option::none();
        }
    }
}
