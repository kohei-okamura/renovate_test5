<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGrade as DomainDwsAreaGrade;
use Domain\DwsAreaGrade\DwsAreaGradeRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsAreaGradeRepository eloquent implementation.
 */
final class DwsAreaGradeRepositoryEloquentImpl extends EloquentRepository implements DwsAreaGradeRepository
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $x = DwsAreaGrade::find($ids);
        return Seq::fromArray($x)->map(fn (DwsAreaGrade $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsAreaGrade
    {
        assert($entity instanceof DomainDwsAreaGrade);
        $x = DwsAreaGrade::fromDomain($entity)->saveIfNotExists();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsAreaGrade::destroy($ids);
    }
}
