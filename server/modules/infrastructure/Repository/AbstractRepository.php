<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Repository;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * Repository ベース実装.
 */
abstract class AbstractRepository implements Repository
{
    /** {@inheritdoc} */
    public function lookup(int ...$ids): Seq
    {
        $xs = $this->lookupHandler(...$ids);
        return count($ids) !== $xs->size() ? Seq::emptySeq() : $xs;
    }

    /** {@inheritdoc} */
    public function remove($entity): void
    {
        $this->removeById($entity->id);
    }

    /**
     * lookupHandler.
     *
     * @param int[] $ids
     * @return \ScalikePHP\Seq
     */
    abstract protected function lookupHandler(int ...$ids): Seq;
}
