<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\OwnExpenseProgram;

use Domain\OwnExpenseProgram\OwnExpenseProgramFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\OwnExpenseProgram\OwnExpenseProgramFinder} Eloquent 実装.
 */
final class OwnExpenseProgramFinderEloquentImpl extends EloquentFinder implements OwnExpenseProgramFinder
{
    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['own_expense_program.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return OwnExpenseProgram::query()
            ->join(
                'own_expense_program_to_attr',
                'own_expense_program.id',
                '=',
                'own_expense_program_to_attr.own_expense_program_id'
            )
            ->join(
                'own_expense_program_attr',
                'own_expense_program_attr.id',
                '=',
                'own_expense_program_to_attr.own_expense_program_attr_id'
            );
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return OwnExpenseProgram::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'officeIdOrNull':
                return $query->where(function (EloquentBuilder $q) use ($value) {
                    $q->orWhere('office_id', $value)
                        ->orWhereNull('office_id');
                });
            case 'officeIds':
                return $query->whereIn('office_id', is_array($value) ? $value : [$value]);
            case 'officeIdsOrNull':
                return $query->where(function (EloquentBuilder $q) use ($value) {
                    $q->whereIn('office_id', is_array($value) ? $value : [$value])
                        ->orWhereNull('office_id');
                });
            case 'q':
                $x = trim($value);
                return strlen($x)
                    ? $this->setKeywordCondition($query, preg_split('/\s/u', $x))
                    : $query;
            default:
                return parent::setCondition($query, $key, $value);
        }
    }
}
