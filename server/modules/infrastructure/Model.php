<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure;

use Infrastructure\Concerns\DomainSupport;
use Infrastructure\Concerns\RelationSupport;

/**
 * Eloquent model base class.
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    use DomainSupport;
    use RelationSupport;

    /** {@inheritdoc} */
    public $timestamps = false;

    /**
     * データベースに存在しない場合のみ保存する.
     *
     * @return static
     */
    public function saveIfNotExists(): self
    {
        if (!$this->exists) {
            $this->save();
        }
        return $this;
    }
}
