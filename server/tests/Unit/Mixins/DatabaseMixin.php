<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Codeception\Test\Unit;

/**
 * Database Mixin.
 *
 * @mixin \Codeception\Test\Unit
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DatabaseMixin
{
    /**
     * データベースに関するの初期化・終了処理を登録する.
     */
    public static function mixinDatabase(): void
    {
        static::beforeEachTest(function (Unit $self): void {
            $app = $self->getModule('Lumen')->getApplication();
            DatabaseMixinSupport::migrateOnce($app);
            DatabaseMixinSupport::fixtureOnce();
        });
        static::beforeEachSpec(function (): void {
            app('db')->beginTransaction();
        });
        static::afterEachSpec(function (): void {
            app('db')->rollBack();
        });
    }
}
