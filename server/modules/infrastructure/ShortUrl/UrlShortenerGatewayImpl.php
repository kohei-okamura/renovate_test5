<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ShortUrl;

use Domain\Config\Config;
use Domain\ShoutUrl\UrlShortenerGateway;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Lib\Exceptions\NetworkIOException;
use Lib\Json;

/**
 * 短縮URL生成実装.
 */
class UrlShortenerGatewayImpl implements UrlShortenerGateway
{
    private HttpClient $client;
    private Config $config;

    public function __construct(HttpClient $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function handle(string $originalUrl): string
    {
        $url = $this->config->get('zinger.url_shortener.url');
        $apiKey = $this->config->get('zinger.url_shortener.api_key');
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'X-Api-Key' => $apiKey,
                ],
                'json' => [
                    'url' => $originalUrl,
                ],
            ]);
        } catch (RequestException $e) {
            $log = $e->getMessage() . \PHP_EOL . urldecode((string)$e->getRequest()->getUri()) . \PHP_EOL . $e->getTraceAsString();
            throw new NetworkIOException('URL Shortener API へのリクエストが失敗しました: ' . $log, $e->getCode(), $e);
        }
        $content = Json::decodeSafety($response->getBody()->getContents(), true)->getOrElse(
            function () use ($response): void {
                throw new NetworkIOException('URL Shortener APIのレスポンスが解析できません。HTTPステータスコード: ' . $response->getStatusCode());
            }
        );
        return $url . $content['token'];
    }
}
