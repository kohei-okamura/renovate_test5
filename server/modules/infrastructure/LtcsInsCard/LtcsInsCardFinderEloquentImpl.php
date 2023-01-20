<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsInsCardFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\LtcsInsCard\LtcsInsCardFinder} Eloquent 実装.
 */
final class LtcsInsCardFinderEloquentImpl extends EloquentFinder implements LtcsInsCardFinder
{
    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['ltcs_ins_card.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return LtcsInsCard::query()
            ->join('ltcs_ins_card_to_attr', 'ltcs_ins_card.id', '=', 'ltcs_ins_card_to_attr.ltcs_ins_card_id')
            ->join('ltcs_ins_card_attr', 'ltcs_ins_card_to_attr.ltcs_ins_card_attr_id', '=', 'ltcs_ins_card_attr.id');
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return LtcsInsCard::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'effectivatedBefore':
                assert($value instanceof Carbon);
                return $query->where('effectivated_on', '<=', $value->toDateString());
            case 'userId':
                return $query->where('user_id', '=', $value);
            case 'userIds':
                return $query->whereIn('user_id', $value);
            default:
                return $query;
        }
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'effectivatedOn':
                return 'effectivated_on';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
