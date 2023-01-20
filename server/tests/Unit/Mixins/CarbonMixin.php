<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Common\Carbon;

/**
 * Carbon Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CarbonMixin
{
    /**
     * Carbon に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCarbon(): void
    {
        static::beforeEachTest(function (): void {
            Carbon::setTestNow('2019-05-15 00:00:00');
        });
        static::afterEachTest(function (): void {
            Carbon::clearTestNow();
        });
    }
}
