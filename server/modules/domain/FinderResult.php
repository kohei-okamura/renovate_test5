<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Closure;
use Domain\Common\Pagination;
use ScalikePHP\Seq;

/**
 * 検索結果.
 *
 * @property-read \Domain\Entity[]|\ScalikePHP\Seq $list
 * @property-read \Domain\Common\Pagination $pagination
 */
final class FinderResult extends Model
{
    /**
     * 検索結果モデルを作成する.
     *
     * @param iterable $list
     * @param \Domain\Common\Pagination $pagination
     * @return self
     */
    public static function from(iterable $list, Pagination $pagination): self
    {
        return self::create([
            'list' => Seq::fromArray($list),
            'pagination' => $pagination,
        ]);
    }

    /**
     * Map function.
     *
     * @param \Closure $f
     * @return self
     */
    public function map(Closure $f)
    {
        return self::from($this->list->map($f), $this->pagination);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'list',
            'pagination',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'list' => true,
            'pagination' => true,
        ];
    }
}
