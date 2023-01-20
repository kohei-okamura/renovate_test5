<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;

/**
 * Mockery Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait MockeryMixin
{
    /**
     * Mockery に関する終了処理を登録する.
     */
    public static function mixinMockery(): void
    {
        static::afterEachSpec(function (): void {
            Mockery::close();
        });
    }
}
