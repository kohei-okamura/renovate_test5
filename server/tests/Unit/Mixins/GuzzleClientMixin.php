<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use GuzzleHttp\Client;
use Mockery;

/**
 * GuzzleClient Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GuzzleClientMixin
{
    /**
     * @var \GuzzleHttp\Client|\Mockery\MockInterface
     */
    protected $client;

    /**
     * GuzzleClient に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGuzzleClient(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(Client::class, fn () => $self->client);
        });
        static::beforeEachSpec(function ($self): void {
            $self->client = Mockery::mock(Client::class);
        });
    }
}
