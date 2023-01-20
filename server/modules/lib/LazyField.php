<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

use InvalidArgumentException;

/**
 * 遅延評価フィールド.
 */
trait LazyField
{
    /**
     * 評価済みの値.
     */
    private array $lazyFieldValues = [];

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, $name) === false) {
            throw new InvalidArgumentException("Field {$name} not exists");
        }
        if (isset($this->lazyFieldValues[$name]) === false) {
            $this->lazyFieldValues[$name] = $this->{$name}();
        }
        return $this->lazyFieldValues[$name];
    }
}
