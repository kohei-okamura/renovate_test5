<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ShoutUrl\UrlShortenerGateway;
use Mockery;

/**
 * {@link \Domain\ShoutUrl\UrlShortenerGateway} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UrlShortenerGatewayMixin
{
    /** @var \Domain\ShoutUrl\UrlShortenerGateway|\Mockery\MockInterface */
    protected $urlShortenerGateway;

    /**
     * UrlShortenerGateway に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUrlShortenerGateway(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UrlShortenerGateway::class, fn () => $self->urlShortenerGateway);
        });
        static::beforeEachSpec(function ($self): void {
            $self->urlShortenerGateway = Mockery::mock(UrlShortenerGateway::class);
        });
    }
}
