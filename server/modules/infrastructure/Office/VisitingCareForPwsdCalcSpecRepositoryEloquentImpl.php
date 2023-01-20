<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\VisitingCareForPwsdCalcSpec as DomainVisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * VisitingCareForPwsdCalcSpecRepository Eloquent Implementation.
 */
class VisitingCareForPwsdCalcSpecRepositoryEloquentImpl extends EloquentRepository implements VisitingCareForPwsdCalcSpecRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = VisitingCareForPwsdCalcSpec::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (VisitingCareForPwsdCalcSpec $x): DomainVisitingCareForPwsdCalcSpec => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainVisitingCareForPwsdCalcSpec
    {
        assert($entity instanceof DomainVisitingCareForPwsdCalcSpec);

        $visitingCareForPwsdCalcSpec = VisitingCareForPwsdCalcSpec::fromDomain($entity)->saveIfNotExists();
        $attr = VisitingCareForPwsdCalcSpecAttr::fromDomain($entity);

        $this->storeVisitingCareForPwsdCalcSpecAttr($visitingCareForPwsdCalcSpec, $attr);

        return $visitingCareForPwsdCalcSpec->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        VisitingCareForPwsdCalcSpecAttr::whereIn('visiting_care_for_pwsd_calc_spec_id', $ids)->delete();
        VisitingCareForPwsdCalcSpec::destroy($ids);
    }

    /**
     * 事業所算定情報（障害・重度訪問介護）属性情報をデータベースに保管する.
     *
     * @param \Infrastructure\Office\VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
     * @param \Infrastructure\Office\VisitingCareForPwsdCalcSpecAttr $attr
     */
    private function storeVisitingCareForPwsdCalcSpecAttr(
        VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec,
        VisitingCareForPwsdCalcSpecAttr $attr
    ): void {
        $visitingCareForPwsdCalcSpec->attr()->save($attr);
    }
}
