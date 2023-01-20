<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProjectServiceMenu as DomainLtcsProjectServiceMenu;
use Domain\Project\LtcsProjectServiceMenuRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * LtcsProjectServiceMenuRepository eloquent implementation.
 */
class LtcsProjectServiceMenuRepositoryEloquentImpl extends EloquentRepository implements LtcsProjectServiceMenuRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsProjectServiceMenu::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsProjectServiceMenu $x): DomainLtcsProjectServiceMenu => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainLtcsProjectServiceMenu
    {
        assert($entity instanceof DomainLtcsProjectServiceMenu);
        $ltcsProjectServiceMenu = LtcsProjectServiceMenu::fromDomain($entity);
        $ltcsProjectServiceMenu->save();
        return $ltcsProjectServiceMenu->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsProjectServiceMenu::destroy($ids);
    }
}
