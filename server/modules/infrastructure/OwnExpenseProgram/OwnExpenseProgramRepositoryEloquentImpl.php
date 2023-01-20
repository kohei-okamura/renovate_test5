<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\OwnExpenseProgram;

use Domain\OwnExpenseProgram\OwnExpenseProgram as DomainOwnExpenseProgram;
use Domain\OwnExpenseProgram\OwnExpenseProgramRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * OwnExpenseProgramRepository eloquent implementation.
 */
final class OwnExpenseProgramRepositoryEloquentImpl extends EloquentRepository implements OwnExpenseProgramRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = OwnExpenseProgram::findMany($ids);
        return Seq::fromArray($xs)->map(fn (OwnExpenseProgram $x): DomainOwnExpenseProgram => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainOwnExpenseProgram
    {
        assert($entity instanceof DomainOwnExpenseProgram);
        $x = OwnExpenseProgram::fromDomain($entity);
        $attr = OwnExpenseProgramAttr::fromDomain($entity);
        $x->saveIfNotExists();
        $x->attr()->save($attr);
        return $x->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        OwnExpenseProgramAttr::whereIn('own_expense_program_id', $ids)->delete();
        OwnExpenseProgram::destroy($ids);
    }
}
