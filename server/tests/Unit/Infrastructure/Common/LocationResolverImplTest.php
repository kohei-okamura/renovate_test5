<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Common;

use Domain\Common\Addr;
use Domain\Common\Prefecture;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Infrastructure\Common\LocationResolverImpl;
use Lib\Exceptions\ExternalApiException;
use Lib\Exceptions\NetworkIOException;
use Lib\Json;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GuzzleClientMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * LocationResolverImpl のテスト.
 */
final class LocationResolverImplTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GuzzleClientMixin;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private const URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    private const GEOCODING_API_KEY = 'xx.xx.xx.xx.xx.xx.xx.xx.xx.xx.xx.xx.xx';

    private LocationResolverImpl $resolver;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $response = new GuzzleResponse(
                IlluminateResponse::HTTP_OK,
                ['Content-Type' => 'application/json; charset=UTF-8'],
                Json::encode($self->normalResponseBody())
            );

            $self->client->allows('get')->andReturn($response)->byDefault();

            $self->config
                ->allows('get')
                ->with('zinger.google.geocoding_api_key')
                ->andReturn(self::GEOCODING_API_KEY)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->resolver = app(LocationResolverImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_resolve(): void
    {
        $this->should(
            'return Some of Location when api resolve exact position',
            function (): void {
                $address = '〒164-0011+tokyo+中野区+中央１丁目３５−６+レッチフィールド中野坂上ビル6F';
                $response = new GuzzleResponse(
                    IlluminateResponse::HTTP_OK,
                    ['Content-Type' => 'application/json; charset=UTF-8'],
                    Json::encode($this->normalResponseBody())
                );
                $this->client
                    ->expects('get')
                    ->with(self::URL, ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]])
                    ->andReturn($response);

                $option = $this->resolver->resolve($this->context, $this->addr());
                /** @var \Domain\Common\Location $location */
                $location = $option->get();
                $this->assertInstanceOf(Some::class, $option);
                $this->assertEquals(['lat' => 35.696903, 'lng' => 139.684812], $location->toAssoc());
            }
        );
        $this->should('log using info', function (): void {
            $address = '〒164-0011+tokyo+中野区+中央１丁目３５−６+レッチフィールド中野坂上ビル6F';
            $response = new GuzzleResponse(
                IlluminateResponse::HTTP_OK,
                ['Content-Type' => 'application/json; charset=UTF-8'],
                Json::encode($this->normalResponseBody())
            );
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->client
                ->expects('get')
                ->with(self::URL, ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]])
                ->andReturn($response);
            $this->logger
                ->expects('info')
                ->with('Google Geocoding API との通信に成功しました', ['url' => self::URL, 'method' => 'GET'] + $context);

            $this->resolver->resolve($this->context, $this->addr());
        });
        $this->should(
            'return None when resolved position is ambiguous or fails',
            function (): void {
                $address = '〒999-9999+tokyo+適当区+適当１丁目３５−６+';
                $response = new GuzzleResponse(
                    IlluminateResponse::HTTP_OK,
                    ['Content-Type' => 'application/json; charset=UTF-8'],
                    Json::encode($this->ambiguousResponseBody())
                );
                $this->client
                    ->expects('get')
                    ->with(self::URL, ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]])
                    ->andReturn($response);

                $actual = $this->resolver->resolve($this->context, $this->ambiguousAddr());

                $this->assertInstanceOf(None::class, $actual);
            }
        );
        $this->should('throws an exception when resolve by api is insufficient', function (): void {
            $address = '〒164-0011+tokyo+中野区+中央１丁目３５−６+レッチフィールド中野坂上ビル6F';
            $response = new GuzzleResponse(IlluminateResponse::HTTP_ACCEPTED, ['Content-Length' => 0]);
            $this->client
                ->expects('get')
                ->with(self::URL, ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]])
                ->andReturn($response);

            $this->assertThrows(
                NetworkIOException::class,
                function (): void {
                    $this->resolver->resolve($this->context, $this->addr());
                }
            );
        });
        $this->should('Throws an exception when a client error occurs', function (): void {
            $address = '〒164-0011+tokyo+中野区+中央１丁目３５−６+レッチフィールド中野坂上ビル6F';
            $options = ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]];
            $this->client
                ->expects('get')
                ->with(self::URL, $options)
                ->andThrow(new ClientException(
                    'client error',
                    new Request('GET', self::URL),
                    new GuzzleResponse()
                ));

            $this->assertThrows(
                NetworkIOException::class,
                function (): void {
                    $this->resolver->resolve($this->context, $this->addr());
                }
            );
        });
        $this->should('Throws an exception when a sever error occurs', function (): void {
            $address = '〒164-0011+tokyo+中野区+中央１丁目３５−６+レッチフィールド中野坂上ビル6F';
            $options = ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]];
            $this->client
                ->expects('get')
                ->with(self::URL, $options)
                ->andThrow(new ServerException(
                    'server error',
                    new Request('GET', self::URL),
                    new GuzzleResponse()
                ));

            $this->assertThrows(
                NetworkIOException::class,
                function (): void {
                    $this->resolver->resolve($this->context, $this->addr());
                }
            );
        });
        $this->should('Throws an exception when a connection error occurs', function (): void {
            $address = '〒164-0011+tokyo+中野区+中央１丁目３５−６+レッチフィールド中野坂上ビル6F';
            $options = ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]];
            $this->client
                ->expects('get')
                ->with(self::URL, $options)
                ->andThrow(new ConnectException('connection error', new Request('GET', self::URL)));

            $this->assertThrows(
                NetworkIOException::class,
                function (): void {
                    $this->resolver->resolve($this->context, $this->addr());
                }
            );
        });
        $this->should('GeoCording status is not OK', function (): void {
            $address = '〒999-9999+tokyo+適当区+適当１丁目３５−６+';
            $response = new GuzzleResponse(
                IlluminateResponse::HTTP_OK,
                ['Content-Type' => 'application/json; charset=UTF-8'],
                Json::encode($this->zeroResultsResponseBody())
            );
            $this->client
                ->allows('get')
                ->with(self::URL, ['query' => ['address' => $address, 'key' => self::GEOCODING_API_KEY]])
                ->andReturn($response);

            $this->assertThrows(
                ExternalApiException::class,
                function (): void {
                    $this->resolver->resolve($this->context, $this->ambiguousAddr());
                }
            );
        });
    }

    /**
     * 特定可能な住所を返す.
     *
     * @return \Domain\Common\Addr
     */
    private function addr(): Addr
    {
        return new Addr(
            postcode: '1640011',
            prefecture: Prefecture::tokyo(),
            city: '中野区',
            street: '中央１丁目３５−６',
            apartment: 'レッチフィールド中野坂上ビル6F',
        );
    }

    /**
     * 特定不可能な住所を返す.
     *
     * @return \Domain\Common\Addr
     */
    private function ambiguousAddr(): Addr
    {
        return new Addr(
            postcode: '９９９９９９９',
            prefecture: Prefecture::tokyo(),
            city: '適当区',
            street: '適当１丁目３５−６',
            apartment: '',
        );
    }

    /**
     * 特定済レスポンスボディ.
     *
     * @return array
     */
    private function normalResponseBody(): array
    {
        return [
            'results' => [
                [
                    'address_components' => [
                        [
                            'long_name' => '６',
                            'short_name' => '６',
                            'types' => ['premise'],
                        ],
                        [
                            'long_name' => '３５',
                            'short_name' => '３５',
                            'types' => ['political', 'sublocality', 'sublocality_level_4'],
                        ],
                        [
                            'long_name' => '１丁目',
                            'short_name' => '１丁目',
                            'types' => ['political', 'sublocality', 'sublocality_level_3'],
                        ],
                        [
                            'long_name' => '中央',
                            'short_name' => '中央',
                            'types' => ['political', 'sublocality', 'sublocality_level_2'],
                        ],
                        [
                            'long_name' => '中野区',
                            'short_name' => '中野区',
                            'types' => ['locality', 'political'],
                        ],
                        [
                            'long_name' => '東京都',
                            'short_name' => '東京都',
                            'types' => ['administrative_area_level_1', 'political'],
                        ],
                        [
                            'long_name' => '日本',
                            'short_name' => 'JP',
                            'types' => ['country', 'political'],
                        ],
                        [
                            'long_name' => '164-0011',
                            'short_name' => '164-0011',
                            'types' => ['postal_code'],
                        ],
                    ],
                    'formatted_address' => '日本、〒164-0011 東京都中野区中央１丁目３５−６ レッチフィールド中野坂上ビル6F',
                    'geometry' => [
                        'location' => [
                            'lat' => 35.696903,
                            'lng' => 139.684812,
                        ],
                        'location_type' => 'ROOFTOP',
                        'viewport' => [
                            'northeast' => [
                                'lat' => 35.6982519802915,
                                'lng' => 139.6861609802915,
                            ],
                            'southwest' => [
                                'lat' => 35.6955540197085,
                                'lng' => 139.6834630197085,
                            ],
                        ],
                    ],
                    'place_id' => 'ChIJfR87ddHyGGARfDozFHib6FQ',
                    'plus_code' => [
                        'compound_code' => 'MMWM+QW 日本、東京都 中野区',
                        'global_code' => '8Q7XMMWM+QW',
                    ],
                    'types' => ['establishment', 'point_of_interest'],
                ],
            ],
            'status' => 'OK',
        ];
    }

    /**
     * 未特定レスポンスボディ.
     *
     * @return array
     */
    private function ambiguousResponseBody(): array
    {
        return [
            'results' => [
                [
                    'address_components' => [
                        [
                            'long_name' => 'カリフォルニア州',
                            'short_name' => 'CA',
                            'types' => ['administrative_area_level_1', 'political'],
                        ],
                        [
                            'long_name' => 'アメリカ合衆国',
                            'short_name' => 'US',
                            'types' => ['country', 'political'],
                        ],
                    ],
                    'formatted_address' => 'アメリカ合衆国 カリフォルニア州',
                    'geometry' => [
                        'bounds' => [
                            'northeast' => [
                                'lat' => 42.0095169,
                                'lng' => -114.131211,
                            ],
                            'southwest' => [
                                'lat' => 32.528832,
                                'lng' => -124.482003,
                            ],
                        ],
                        'location' => [
                            'lat' => 36.778261,
                            'lng' => -119.4179324,
                        ],
                        'location_type' => 'APPROXIMATE',
                        'viewport' => [
                            'northeast' => [
                                'lat' => 42.0095169,
                                'lng' => -114.131211,
                            ],
                            'southwest' => [
                                'lat' => 32.528832,
                                'lng' => -124.482003,
                            ],
                        ],
                    ],
                    'partial_match' => true,
                    'place_id' => 'ChIJPV4oX_65j4ARVW8IJ6IJUYs',
                    'types' => ['administrative_area_level_1', 'political'],
                ],
            ],
            'status' => 'OK',
        ];
    }

    /**
     * GeocodingのステータスがOK以外のレスポンスボディ.
     *
     * @return array
     */
    private function zeroResultsResponseBody(): array
    {
        return [
            'results' => [],
            'status' => 'ZERO_RESULTS',
        ];
    }
}
