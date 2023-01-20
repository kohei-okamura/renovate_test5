<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Closure;
use Domain\Common\Decimal;
use Domain\Equatable;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use JsonSerializable;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票：合計.
 *
 * {@link \Domain\Billing\DwsBillingServiceReportAggregateGroup} をキー
 * {@link \Domain\Billing\DwsBillingServiceReportAggregateItem} を値とするマップの実装.
 */
final class DwsBillingServiceReportAggregate implements JsonSerializable, Equatable
{
    /** @var array|\Domain\Billing\DwsBillingServiceReportAggregateItem[] */
    private array $assoc;

    /**
     * {@link \Domain\Billing\DwsBillingServiceReportAggregate} Constructor.
     *
     * @param array $values
     */
    private function __construct(array $values)
    {
        $this->assoc = $values;
    }

    /**
     * 居宅介護向けのインスタンスを生成する.
     *
     * @see https://github.com/eustylelab/zinger/blob/073aab7c181d9fcaacca7e960a5a384f82335df9/server/modules/useCase/Billing/CreateDwsBillingServiceReportInteractor.php#L568-L718
     * @param \Domain\Billing\DwsHomeHelpServiceUnit[]&\ScalikePHP\Seq $units
     * @return static
     */
    public static function forHomeHelpService(Seq $units): self
    {
        $groupedUnits = $units->groupBy(fn (DwsHomeHelpServiceUnit $x): int => $x->category->value());

        /**
         * サービスコード区分を指定してサービス実績単位を抽出する.
         *
         * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
         * @return \Domain\Billing\DwsHomeHelpServiceUnit[]&\ScalikePHP\Seq
         */
        $extractUnitsByCategory = fn (DwsServiceCodeCategory $category): Seq => $groupedUnits
            ->get($category->value())
            ->toSeq()
            ->flatten();

        /**
         * サービス実績単位を提供者区分ごとに分類する.
         *
         * @param \ScalikePHP\Seq $xs
         * @return \Domain\Billing\DwsHomeHelpServiceUnit[][]|\ScalikePHP\Map|\ScalikePHP\Seq[]
         */
        $groupByProviderType = fn (Seq $xs): Map => $xs->groupBy(
            fn (DwsHomeHelpServiceUnit $x): int => $x->fragment->providerType->value()
        );

        $physicalCareSeq = $extractUnitsByCategory(DwsServiceCodeCategory::physicalCare());
        $physicalCareMap = $groupByProviderType($physicalCareSeq);

        $accompanyWithPhysicalCareSeq = $extractUnitsByCategory(DwsServiceCodeCategory::accompanyWithPhysicalCare());
        $accompanyWithPhysicalCareMap = $groupByProviderType($accompanyWithPhysicalCareSeq);

        $houseworkSeq = $extractUnitsByCategory(DwsServiceCodeCategory::housework());
        $houseworkMap = $groupByProviderType($houseworkSeq);

        $accompanySeq = $extractUnitsByCategory(DwsServiceCodeCategory::accompany());
        $accompanyMap = $groupByProviderType($accompanySeq);

        $accessibleTaxiSeq = $extractUnitsByCategory(DwsServiceCodeCategory::accessibleTaxi());
        $accessibleTaxiMap = $groupByProviderType($accessibleTaxiSeq);

        /**
         * 合計時間を計算（集計）する.
         *
         * @param \Domain\Billing\DwsHomeHelpServiceUnit ...$units
         * @return \Domain\Common\Decimal
         */
        $computeHours = function (DwsHomeHelpServiceUnit ...$units): Decimal {
            $value = Seq::from(...$units)
                ->map(fn (DwsHomeHelpServiceUnit $x): int => $x->getServiceDurationHours() * $x->fragment->headcount)
                ->sum();
            return Decimal::fromInt($value);
        };

        /**
         * 合計回数を計算（集計）する.
         *
         * @param \Domain\Billing\DwsHomeHelpServiceUnit ...$units
         * @return \Domain\Common\Decimal
         */
        $computeCount = function (DwsHomeHelpServiceUnit ...$units): Decimal {
            $value = count($units);
            return Decimal::fromInt($value);
        };

        /**
         * 提供者区分を指定してサービス実績単位の一覧を取得する.
         *
         * @param \ScalikePHP\Map $map
         * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $providerType
         * @return \Domain\Billing\DwsHomeHelpServiceUnit[]&\ScalikePHP\Seq
         */
        $getFromMap = fn (Map $map, DwsHomeHelpServiceProviderType $providerType): Seq => $map->getOrElse(
            $providerType->value(),
            fn (): Seq => Seq::empty()
        );

        return self::fromAssoc([
            DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => $computeHours(
                    ...$getFromMap($physicalCareMap, DwsHomeHelpServiceProviderType::none()),
                ),
                DwsBillingServiceReportAggregateCategory::category70()->value() => $computeHours(
                    ...$getFromMap($physicalCareMap, DwsHomeHelpServiceProviderType::beginner()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => $computeHours(
                    ...$getFromMap($physicalCareMap, DwsHomeHelpServiceProviderType::careWorkerForPwsd()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $computeHours(
                    ...$physicalCareSeq
                ),
            ],
            DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => $computeHours(
                    ...$getFromMap($accompanyWithPhysicalCareMap, DwsHomeHelpServiceProviderType::none()),
                ),
                DwsBillingServiceReportAggregateCategory::category70()->value() => $computeHours(
                    ...$getFromMap($accompanyWithPhysicalCareMap, DwsHomeHelpServiceProviderType::beginner()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => $computeHours(
                    ...$getFromMap($accompanyWithPhysicalCareMap, DwsHomeHelpServiceProviderType::careWorkerForPwsd()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $computeHours(
                    ...$accompanyWithPhysicalCareSeq
                ),
            ],
            DwsBillingServiceReportAggregateGroup::housework()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => $computeHours(
                    ...$getFromMap($houseworkMap, DwsHomeHelpServiceProviderType::none()),
                ),
                DwsBillingServiceReportAggregateCategory::category90()->value() => $computeHours(
                    ...$getFromMap($houseworkMap, DwsHomeHelpServiceProviderType::beginner()),
                    ...$getFromMap($houseworkMap, DwsHomeHelpServiceProviderType::careWorkerForPwsd()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $computeHours(
                    ...$houseworkSeq
                ),
            ],
            DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => $computeHours(
                    ...$getFromMap($accompanyMap, DwsHomeHelpServiceProviderType::none()),
                ),
                DwsBillingServiceReportAggregateCategory::category90()->value() => $computeHours(
                    ...$getFromMap($accompanyMap, DwsHomeHelpServiceProviderType::beginner()),
                    ...$getFromMap($accompanyMap, DwsHomeHelpServiceProviderType::careWorkerForPwsd()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $computeHours(
                    ...$accompanySeq
                ),
            ],
            // 合計5 だけ時間数ではなく回数
            DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => $computeCount(
                    ...$getFromMap($accessibleTaxiMap, DwsHomeHelpServiceProviderType::none()),
                ),
                DwsBillingServiceReportAggregateCategory::category90()->value() => $computeCount(
                    ...$getFromMap($accessibleTaxiMap, DwsHomeHelpServiceProviderType::beginner()),
                    ...$getFromMap($accessibleTaxiMap, DwsHomeHelpServiceProviderType::careWorkerForPwsd()),
                ),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $computeCount(
                    ...$accessibleTaxiSeq
                ),
            ],
        ]);
    }

    /**
     * 重度訪問介護向けのインスタンスを生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdUnit[]&\ScalikePHP\Seq $units
     * @return static
     */
    public static function forVisitingCareForPwsd(Seq $units): self
    {
        // `$units` に `null` はない……と思うけどオリジナルのコードを尊重して念のためフィルタする
        // See https://github.com/eustylelab/zinger/blob/073aab7c181d9fcaacca7e960a5a384f82335df9/server/modules/useCase/Billing/CreateDwsBillingServiceReportInteractor.php#L135
        $xs = $units->filter(fn (?DwsVisitingCareForPwsdUnit $x): bool => $x !== null)->computed();

        $f = fn (Closure $g): Decimal => Decimal::fromInt(
            $xs->map(fn (DwsVisitingCareForPwsdUnit $x): int => $g($x) * $x->fragment->headcount)->sum()
        );

        return self::fromAssoc([
            DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $f(
                    fn (DwsVisitingCareForPwsdUnit $x): int => $x->getServiceDurationHours()
                ),
            ],
            DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => $f(
                    fn (DwsVisitingCareForPwsdUnit $x): int => $x->getMovingDurationHours()
                ),
            ],
        ]);
    }

    /**
     * Create from an assoc.
     *
     * @param array $assoc
     * @return static
     */
    public static function fromAssoc(array $assoc): self
    {
        $map = Map::from($assoc);
        $map->keys()
            ->find(fn (int $group): bool => !DwsBillingServiceReportAggregateGroup::isValid($group))
            ->each(function (int $invalidGroupValue): void {
                throw new InvalidArgumentException("Invalid group given: {$invalidGroupValue}");
            });
        $values = $map
            ->filter(fn (array $item): bool => !empty($item))
            ->mapValues(function (array $item): DwsBillingServiceReportAggregateItem {
                return DwsBillingServiceReportAggregateItem::fromAssoc($item);
            })
            ->toAssoc();
        return new self($values);
    }

    /**
     * 合計区分グループ・合計区分カテゴリーに対応する値を返す.
     *
     * @param \Domain\Billing\DwsBillingServiceReportAggregateGroup $group
     * @param \Domain\Billing\DwsBillingServiceReportAggregateCategory $category
     * @return \Domain\Common\Decimal
     */
    public function get(
        DwsBillingServiceReportAggregateGroup $group,
        DwsBillingServiceReportAggregateCategory $category
    ): Decimal {
        return Option::fromArray($this->assoc, $group->value())
            ->flatMap(
                fn (DwsBillingServiceReportAggregateItem $item): Option => $item->getOption($category)
            )
            ->getOrElse(fn (): Decimal => Decimal::zero());
    }

    /**
     * 連想配列に変換する.
     *
     * @return array
     */
    public function toAssoc(): array
    {
        return Map::from($this->assoc)
            ->mapValues(fn (DwsBillingServiceReportAggregateItem $x): array => $x->toAssoc())
            ->toAssoc();
    }

    /** {@inheritdoc} */
    public function jsonSerialize(): array
    {
        return $this->assoc;
    }

    /** {@inheritdoc} */
    public function equals(mixed $that): bool
    {
        return $that instanceof self
            && empty(array_diff_key($this->assoc, $that->assoc))
            && empty(array_diff_key($that->assoc, $this->assoc))
            && Seq::from(...array_keys($this->assoc))
                ->forAll(fn (int $key): bool => $this->assoc[$key]->equals($that->assoc[$key]));
    }
}
