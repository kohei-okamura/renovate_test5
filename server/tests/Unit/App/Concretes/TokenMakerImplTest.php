<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Concretes;

use App\Concretes\TokenMakerImpl;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * TokenMakeImpl のテスト
 */
class TokenMakerImplTest extends Test
{
    use UnitSupport;

    private TokenMakerImpl $maker;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (TokenMakerImplTest $self): void {
            $self->maker = app(TokenMakerImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_make(): void
    {
        $this->should('return non-null string', function (): void {
            $this->assertNotNull($this->maker->make(mt_rand(0, 100)));
        });

        $this->should('return specified length string', function (): void {
            $length = mt_rand(0, 50);
            $this->assertEquals($length, strlen($this->maker->make($length)));
        });
    }
}
