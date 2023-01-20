<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Concretes;

use App\Concretes\DefaultTransactionManager;
use Closure;
use Mockery;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DefaultTransactionManager のテスト.
 */
class DefaultTransactionManagerTest extends Test
{
    use MockeryMixin;
    use UnitSupport;

    private DefaultTransactionManager $manager;
    private Closure $callbackSpy;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DefaultTransactionManagerTest $self): void {
            $self->manager = app(DefaultTransactionManager::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_run(): void
    {
        $this->should('run callback', function (): void {
            $f = Mockery::spy(fn () => 'RUN CALLBACK');
            $g = fn () => call_user_func($f);

            $actual = $this->manager->run($g);

            $this->assertSame('RUN CALLBACK', $actual);
            $f->shouldHaveReceived('__invoke');
        });
    }
}
