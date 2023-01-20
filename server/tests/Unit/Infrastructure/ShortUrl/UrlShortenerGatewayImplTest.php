<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ShortUrl;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Infrastructure\ShortUrl\UrlShortenerGatewayImpl;
use Lib\Exceptions\NetworkIOException;
use Lib\Json;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GuzzleClientMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ShortUrl\UrlShortenerGatewayImpl} Test.
 */
final class UrlShortenerGatewayImplTest extends Test
{
    use ConfigMixin;
    use GuzzleClientMixin;
    use MockeryMixin;
    use UnitSupport;

    private const URL = 'http://short.test/';
    private const API_KEY = 'api-key';
    private const TOKEN = 'generate-token';

    private UrlShortenerGatewayImpl $impl;
    private GuzzleResponse $response;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UrlShortenerGatewayImplTest $self): void {
            $self->response = new GuzzleResponse(
                IlluminateResponse::HTTP_OK,
                ['Content-Type' => 'application/json; charset=UTF-8'],
                Json::encode(['token' => self::TOKEN])
            );

            $self->config
                ->allows('get')
                ->with('zinger.url_shortener.url')
                ->andReturn(self::URL)
                ->byDefault();
            $self->config
                ->allows('get')
                ->with('zinger.url_shortener.api_key')
                ->andReturn(self::API_KEY)
                ->byDefault();
            $self->client
                ->allows('post')
                ->andReturn($self->response)
                ->byDefault();

            $self->impl = app(UrlShortenerGatewayImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return generate url string', function (): void {
            $this->assertEquals(
                self::URL . self::TOKEN,
                $this->impl->handle('any')
            );
        });
        $this->should('use GuzzleHttp module when calling API', function (): void {
            $original = 'http://original.url.test';
            $this->client
                ->expects('post')
                ->with(
                    self::URL,
                    [
                        'headers' => [
                            'X-Api-Key' => self::API_KEY,
                        ],
                        'json' => [
                            'url' => $original,
                        ],
                    ],
                )
                ->andReturn($this->response);

            $this->assertEquals(
                self::URL . self::TOKEN,
                $this->impl->handle($original)
            );
        });
        $this->should(
            'throw Exception when throw Exception in GuzzleHttp module',
            function (): void {
                $this->client
                    ->expects('post')
                    ->andThrow(new RequestException(
                        'Exception',
                        new Request('POST', self::URL)
                    ));

                $this->assertThrows(
                    NetworkIOException::class,
                    function (): void {
                        $this->impl->handle('any');
                    }
                );
            }
        );
        $this->should(
            'throw Exception when GuzzleHttp module return empty string',
            function (): void {
                $response = new GuzzleResponse(
                    IlluminateResponse::HTTP_OK,
                    ['Content-Type' => 'application/json; charset=UTF-8'],
                    ''
                );
                $this->client
                    ->expects('post')
                    ->andReturn($response);

                $this->assertThrows(
                    NetworkIOException::class,
                    function (): void {
                        $this->impl->handle('any');
                    }
                );
            }
        );
    }
}
