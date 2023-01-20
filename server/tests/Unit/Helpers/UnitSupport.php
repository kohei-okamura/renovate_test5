<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Closure;
use Codeception\Specify;

/**
 * ユニットテスト共通処理.
 *
 * @mixin \Codeception\Test\Unit
 */
trait UnitSupport
{
    use AssertArrayStrictEquals;
    use AssertEach;
    use AssertExists;
    use AssertForAll;
    use AssertMailViewExists;
    use AssertMatchesModelSnapshot;
    use AssertModelStrictEquals;
    use AssertNone;
    use AssertSome;
    use AssertThrows;
    use Invert;
    use Specify;

    protected static $beforeEachTest = [];
    protected static $beforeEachSpec = [];
    protected static $afterEachSpec = [];
    protected static $afterEachTest = [];

    /** {@inheritdoc} */
    protected function _before(): void
    {
        foreach (static::$beforeEachTest as $f) {
            $f($this);
        }
        foreach (static::$beforeEachSpec as $f) {
            $this->beforeSpecify(function () use ($f): void {
                $f($this);
            });
        }
        foreach (static::$afterEachSpec as $f) {
            $this->afterSpecify(function () use ($f): void {
                $f($this);
            });
        }
    }

    /** {@inheritdoc} */
    protected function _after(): void
    {
        foreach (static::$afterEachTest as $f) {
            $f($this);
        }
        $this->cleanSpecify();
    }

    /**
     * 各テストメソッドの実行前に行う処理を定義する.
     *
     * @param \Closure $f
     * @return void
     */
    protected static function beforeEachTest(Closure $f): void
    {
        array_push(static::$beforeEachTest, $f);
    }

    /**
     * 各テストスペックの実行前に行う処理を定義する.
     *
     * @param \Closure $f
     * @return void
     */
    protected static function beforeEachSpec(Closure $f): void
    {
        array_push(static::$beforeEachSpec, $f);
    }

    /**
     * 各テストスペックの実行後に行う処理を定義する.
     *
     * @param \Closure $f
     * @return void
     */
    protected static function afterEachSpec(Closure $f): void
    {
        array_unshift(static::$afterEachSpec, $f);
    }

    /**
     * 各テストメソッドの実行後に行う処理を定義する.
     *
     * @param \Closure $f
     * @return void
     */
    protected static function afterEachTest(Closure $f): void
    {
        array_unshift(static::$afterEachTest, $f);
    }
}
