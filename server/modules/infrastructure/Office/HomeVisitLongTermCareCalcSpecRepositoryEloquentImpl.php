<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeVisitLongTermCareCalcSpec as DomainHomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * HomeVisitLongTermCareCalcSpecRepository Eloquent Implementation.
 */
class HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl extends EloquentRepository implements HomeVisitLongTermCareCalcSpecRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = HomeVisitLongTermCareCalcSpec::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (HomeVisitLongTermCareCalcSpec $x): DomainHomeVisitLongTermCareCalcSpec => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainHomeVisitLongTermCareCalcSpec
    {
        assert($entity instanceof DomainHomeVisitLongTermCareCalcSpec);

        $homeVisitLongTermCareCalcSpec = HomeVisitLongTermCareCalcSpec::fromDomain($entity)->saveIfNotExists();
        $attr = HomeVisitLongTermCareCalcSpecAttr::fromDomain($entity);

        $this->storeHomeVisitLongTermCareCalcSpecAttr($homeVisitLongTermCareCalcSpec, $attr);

        return $homeVisitLongTermCareCalcSpec->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        HomeVisitLongTermCareCalcSpecAttr::whereIn('home_visit_long_term_care_calc_spec_id', $ids)->delete();
        HomeVisitLongTermCareCalcSpec::destroy($ids);
    }

    /**
     * 事業所算定情報（介保・訪問介護）属性情報をデータベースに保管する.
     *
     * @param \Infrastructure\Office\HomeVisitLongTermCareCalcSpec $homeVisitLongTermCareCalcSpec
     * @param \Infrastructure\Office\HomeVisitLongTermCareCalcSpecAttr $attr
     */
    private function storeHomeVisitLongTermCareCalcSpecAttr(
        HomeVisitLongTermCareCalcSpec $homeVisitLongTermCareCalcSpec,
        HomeVisitLongTermCareCalcSpecAttr $attr
    ): void {
        $homeVisitLongTermCareCalcSpec->attr()->save($attr);
    }
}
