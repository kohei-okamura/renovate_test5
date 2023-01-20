<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Addr;
use Domain\Common\Location;
use Domain\Common\LocationResolver;
use Domain\Config\Config;
use Domain\Context\Context;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Lib\Exceptions\ExternalApiException;
use Lib\Exceptions\NetworkIOException;
use Lib\Json;
use Lib\Logging;
use ScalikePHP\Option;

/**
 * LocationResolver implementation.
 */
final class LocationResolverImpl implements LocationResolver
{
    use Logging;

    private const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    private HttpClient $client;
    private Config $config;

    /**
     * LocationResolverImpl constructor.
     *
     * @param \GuzzleHttp\Client $client
     * @param \Domain\Config\Config $config
     */
    public function __construct(HttpClient $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Google Geocoding API を利用して位置情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Addr $addr
     * @return \Domain\Common\Location[]|\ScalikePHP\Option
     */
    public function resolve(Context $context, Addr $addr): Option
    {
        $data = $this->resolveLocation($addr, $this->config->get('zinger.google.geocoding_api_key'));
        $this->logger()->info(
            'Google Geocoding API との通信に成功しました',
            ['url' => self::URL, 'method' => 'GET'] + $context->logContext()
        );
        return $this->parseLocation($context, $addr, $data);
    }

    /**
     * API から位置情報を取得する.
     *
     * @param \Domain\Common\Addr $addr
     * @param string $key
     * @throws \Lib\Exceptions\NetworkIOException
     * @throws \GuzzleHttp\Exception\RequestException
     * @return array
     */
    private function resolveLocation(Addr $addr, string $key): array
    {
        $addrString = $this->createAddrString($addr);
        try {
            $options = [
                'query' => [
                    'address' => $addrString,
                    'key' => $key,
                ],
            ];
            $response = $this->client->get(self::URL, $options);
        } catch (RequestException|ConnectException $e) {
            $log = $e->getMessage() . \PHP_EOL . urldecode((string)$e->getRequest()->getUri()) . \PHP_EOL . $e->getTraceAsString();
            throw new NetworkIOException('Geocoding API へのリクエストが失敗しました: ' . $log, $e->getCode(), $e);
        }
        $content = $response->getBody()->getContents();
        if ($content === '') {
            throw new NetworkIOException('Geocoding API からのレスポンスが空です。HTTPステータスコード: ' . $response->getStatusCode());
        }
        return Json::decode($content, true);
    }

    /**
     * API から取得した位置情報をパースする.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Addr $addr
     * @param array $data
     * @return \Domain\Common\Location[]|\ScalikePHP\Option
     */
    private function parseLocation(Context $context, Addr $addr, array $data): Option
    {
        if ($data['status'] !== 'OK') {
            $status = $data['status'];
            $errorMessage = $data['error_message'] ?? '';
            throw new ExternalApiException("Geocoding API から OK 以外のレスポンスを受け取りました。[{$status}]: {$errorMessage}");
        }

        $locationType = $data['results'][0]['geometry']['location_type'];
        if ($locationType !== 'ROOFTOP') {
            $input = $this->createAddrString($addr);
            $logContext = [
                'url' => self::URL,
                'method' => 'GET',
                'addr' => $input,
                'location_type' => $locationType,
            ];
            $this->logger()->info('住所を特定できませんでした。', $logContext + $context->logContext());
            return Option::none();
        }
        return Option::from(Location::create([
            'lat' => $data['results'][0]['geometry']['location']['lat'],
            'lng' => $data['results'][0]['geometry']['location']['lng'],
        ]));
    }

    /**
     * 住所のパラメータを作成する.
     *
     * @param \Domain\Common\Addr $addr
     * @return string
     */
    private function createAddrString(Addr $addr): string
    {
        $postCode = $this->formatPostCode($addr);
        return "{$postCode}+{$addr->prefecture->key()}+{$addr->city}+{$addr->street}+{$addr->apartment}";
    }

    /**
     * 郵便番号を整形する.
     *
     * @param \Domain\Common\Addr $addr
     * @return string
     */
    private function formatPostCode(Addr $addr): string
    {
        $numbers = preg_replace('/[^0-9]/u', '', mb_convert_kana($addr->postcode, 'n'));
        return '〒' . substr_replace($numbers, '-', 3, 0);
    }
}
