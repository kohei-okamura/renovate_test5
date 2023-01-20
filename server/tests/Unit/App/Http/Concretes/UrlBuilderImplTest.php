<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Concretes;

use App\Concretes\UrlBuilderImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Concretes\UrlBuilderImpl} Test.
 */
class UrlBuilderImplTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private const HOST = '%s.test.host';
    private const PATH = '/test.path';
    private UrlBuilderImpl $urlBuilder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UrlBuilderImplTest $self): void {
            $self->config
                ->allows('get')
                ->andReturn(self::HOST)
                ->byDefault();
            $self->urlBuilder = app(UrlBuilderImpl::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_build(): void
    {
        $this->should('return url', function (): void {
            $url = sprintf(
                'https://' . self::HOST . self::PATH,
                $this->context->organization->code,
            );
            $this->assertSame(
                $url,
                $this->urlBuilder->build($this->context, self::PATH)
            );
        });
        $this->should('use Config', function (): void {
            $this->config
                ->expects('get')
                ->with('zinger.host')
                ->andReturn(self::HOST);
            $this->urlBuilder->build($this->context, self::PATH);
        });
    }
}
