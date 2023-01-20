<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

/**
 * Dependencies Interface.
 */
interface DependenciesInterface
{
    /**
     * 依存性のリストを取得する.
     *
     * @return array
     */
    public function getDependenciesList(): iterable;
}
