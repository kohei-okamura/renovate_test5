<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGrade as DomainLtcsAreaGrade;
use Domain\LtcsAreaGrade\LtcsAreaGradeRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * LtcsAreaGradeRepository eloquent implementation.
 */
final class LtcsAreaGradeRepositoryEloquentImpl extends EloquentRepository implements LtcsAreaGradeRepository
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$id): Seq
    {
        $x = LtcsAreaGrade::find($id);
        return Seq::fromArray($x)->map(fn (LtcsAreaGrade $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainLtcsAreaGrade
    {
        assert($entity instanceof DomainLtcsAreaGrade);
        $x = LtcsAreaGrade::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsAreaGrade::destroy($ids);
    }
}
