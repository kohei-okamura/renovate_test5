<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGradeFee as DomainDwsAreaGradeFee;
use Domain\DwsAreaGrade\DwsAreaGradeFeeRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsAreaGradeFeeRepository Eloquent Implementation.
 */
class DwsAreaGradeFeeRepositoryEloquentImpl extends EloquentRepository implements DwsAreaGradeFeeRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $s = DwsAreaGradeFee::find($ids);
        return Seq::fromArray($s)->map(fn (DwsAreaGradeFee $x): DomainDwsAreaGradeFee => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsAreaGradeFee
    {
        assert($entity instanceof DomainDwsAreaGradeFee);
        $x = DwsAreaGradeFee::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsAreaGradeFee::destroy($ids);
    }
}
