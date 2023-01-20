<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Sms\SmsGateway;
use Mockery;

/**
 * {@link \Domain\Sms\SmsGateway} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SmsGatewayMixin
{
    /** @var \Domain\Sms\SmsGateway|\Mockery\MockInterface */
    protected $smsGateway;

    /**
     * SmsGateway に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSmsGateway(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(SmsGateway::class, fn () => $self->smsGateway);
        });
        static::beforeEachSpec(function ($self): void {
            $self->smsGateway = Mockery::mock(SmsGateway::class);
        });
    }
}
