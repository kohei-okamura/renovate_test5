<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGradeFee as DomainLtcsAreaGradeFee;
use Domain\LtcsAreaGrade\LtcsAreaGradeFeeRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * LtcsAreaGradeFeeRepository Eloquent Implementation.
 */
class LtcsAreaGradeFeeRepositoryEloquentImpl extends EloquentRepository implements LtcsAreaGradeFeeRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $s = LtcsAreaGradeFee::find($ids);
        return Seq::fromArray($s)->map(fn (LtcsAreaGradeFee $x): DomainLtcsAreaGradeFee => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainLtcsAreaGradeFee
    {
        assert($entity instanceof DomainLtcsAreaGradeFee);
        $x = LtcsAreaGradeFee::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsAreaGradeFee::destroy($ids);
    }
}
