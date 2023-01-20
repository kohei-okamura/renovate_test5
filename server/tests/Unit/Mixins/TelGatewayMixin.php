<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Tel\TelGateway;
use Mockery;

/**
 * TelGateway Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait TelGatewayMixin
{
    /** @var \Domain\Tel\TelGateway|\Mockery\MockInterface */
    protected $telGateway;

    /**
     * TelGateway に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinTelGateway(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(TelGateway::class, fn () => $self->telGateway);
        });
        static::beforeEachSpec(function ($self): void {
            $self->telGateway = Mockery::mock(TelGateway::class);
        });
    }
}
