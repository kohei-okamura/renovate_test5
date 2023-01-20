<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProjectServiceMenu as DomainDwsProjectServiceMenu;
use Domain\Project\DwsProjectServiceMenuRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsProjectServiceMenuRepository eloquent implementation.
 */
class DwsProjectServiceMenuRepositoryEloquentImpl extends EloquentRepository implements DwsProjectServiceMenuRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsProjectServiceMenu::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsProjectServiceMenu $x): DomainDwsProjectServiceMenu => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsProjectServiceMenu
    {
        assert($entity instanceof DomainDwsProjectServiceMenu);
        $dwsProjectServiceMenu = DwsProjectServiceMenu::fromDomain($entity);
        $dwsProjectServiceMenu->save();
        return $dwsProjectServiceMenu->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsProjectServiceMenu::destroy($ids);
    }
}
