<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Decimal;
use Domain\Equatable;
use JsonSerializable;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票：合計：明細.
 *
 * {@link \Domain\Billing\DwsBillingServiceReportAggregateCategory} をキー, 小数値を値とするマップの実装.
 */
final class DwsBillingServiceReportAggregateItem implements JsonSerializable, Equatable
{
    /** @var array|\Domain\Common\Decimal[] */
    private array $assoc;

    /**
     * Constructor.
     *
     * @param array $assoc
     */
    private function __construct(array $assoc)
    {
        $this->assoc = $assoc;
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
            ->find(fn (int $category): bool => !DwsBillingServiceReportAggregateCategory::isValid($category))
            ->each(function (int $invalidCategoryValue): void {
                throw new InvalidArgumentException("Invalid category given: {$invalidCategoryValue}");
            });
        $map->values()
            ->find(fn ($x): bool => !($x instanceof Decimal))
            ->each(function ($invalidValue): void {
                throw new InvalidArgumentException("Invalid value given: {$invalidValue}");
            });
        return new self($assoc);
    }

    /**
     * 合計区分カテゴリーに対応する合計時間数を返す.
     *
     * @param \Domain\Billing\DwsBillingServiceReportAggregateCategory $category
     * @return \Domain\Common\Decimal[]&\ScalikePHP\Option
     */
    public function getOption(DwsBillingServiceReportAggregateCategory $category): Option
    {
        $categoryId = $category->value();
        return Option::fromArray($this->assoc, $categoryId);
    }

    /**
     * 連想配列に変換する.
     *
     * @return array
     */
    public function toAssoc(): array
    {
        return Map::from($this->assoc)->toAssoc();
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
