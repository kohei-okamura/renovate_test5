<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeHelpServiceCalcSpec as DomainHomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceCalcSpecRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * HomeHelpServiceCalcSpecRepository Eloquent Implementation.
 */
class HomeHelpServiceCalcSpecRepositoryEloquentImpl extends EloquentRepository implements HomeHelpServiceCalcSpecRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = HomeHelpServiceCalcSpec::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (HomeHelpServiceCalcSpec $x): DomainHomeHelpServiceCalcSpec => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainHomeHelpServiceCalcSpec
    {
        assert($entity instanceof DomainHomeHelpServiceCalcSpec);

        $homeHelpServiceCalcSpec = HomeHelpServiceCalcSpec::fromDomain($entity)->saveIfNotExists();
        $attr = HomeHelpServiceCalcSpecAttr::fromDomain($entity);

        $this->storeHomeHelpServiceCalcSpecAttr($homeHelpServiceCalcSpec, $attr);

        return $homeHelpServiceCalcSpec->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        HomeHelpServiceCalcSpecAttr::whereIn('home_help_service_calc_spec_id', $ids)->delete();
        HomeHelpServiceCalcSpec::destroy($ids);
    }

    /**
     * 事業所算定情報（障害・居宅介護）属性情報をデータベースに保管する.
     *
     * @param \Infrastructure\Office\HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec
     * @param \Infrastructure\Office\HomeHelpServiceCalcSpecAttr $attr
     */
    private function storeHomeHelpServiceCalcSpecAttr(
        HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        HomeHelpServiceCalcSpecAttr $attr
    ): void {
        $homeHelpServiceCalcSpec->attr()->save($attr);
    }
}
