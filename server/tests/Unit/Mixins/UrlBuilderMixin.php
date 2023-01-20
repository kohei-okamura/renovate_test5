<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Url\UrlBuilder;
use Mockery;

/**
 * UrlBuilder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UrlBuilderMixin
{
    /**
     * @var \Domain\Url\UrlBuilder|\Mockery\MockInterface
     */
    protected $urlBuilder;

    /**
     * {@link \Domain\Url\UrlBuilder} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinUrlBuilder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UrlBuilder::class, fn () => $self->urlBuilder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->urlBuilder = Mockery::mock(UrlBuilder::class);
        });
    }
}
