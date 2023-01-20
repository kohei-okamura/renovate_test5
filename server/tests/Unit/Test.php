<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit;

use App\Providers\AppServiceProvider;
use App\Providers\Dependencies\DependenciesInterface;
use Codeception\Test\Unit;
use Mockery;
use ReflectionClass;
use ReflectionMethod;

/**
 * テスト規定クラス.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
abstract class Test extends Unit implements Fixtures
{
    /**
     * 全テストクラス共通のセットアップ処理.
     */
    final public static function _setUpBeforeClass(): void
    {
        foreach (AppServiceProvider::DEPENDENCIES_CLASSES as $dependenciesClass) {
            self::registerDependencies($dependenciesClass);
        }
        $reflection = new ReflectionClass(static::class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC);
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'mixin') === 0) {
                $method->invoke(null);
            }
        }
        static::_setUpSuite();
    }

    /**
     * 各テストクラス固有のセットアップ処理.
     */
    protected static function _setUpSuite(): void
    {
    }

    /**
     * Register dependencies.
     *
     * @param string $dependenciesClass
     */
    private static function registerDependencies(string $dependenciesClass): void
    {
        $dependencies = app($dependenciesClass);
        assert($dependencies instanceof DependenciesInterface);
        $list = $dependencies->getDependenciesList();
        foreach ($list as $abstract => $concrete) {
            static::beforeEachTest(fn () => app()->bind($abstract, fn () => Mockery::mock($abstract)));
        }
    }
}
