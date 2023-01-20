<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Config\Config;
use Domain\Enum;
use Domain\FinderResult;
use Domain\Model;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as DomainEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\Timeframe;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Cache\CacheManager;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\SetupException;
use Lib\Json;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\BuildFinderResultHolder;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder} 実装.
 */
final class DwsVisitingCareForPwsdDictionaryEntryFinderImpl implements DwsVisitingCareForPwsdDictionaryEntryFinder
{
    use BuildFinderResultHolder;

    private HttpClient $client;
    private CacheManager $cache;

    /**
     * LocationResolverImpl constructor.
     *
     * @param \Domain\Config\Config $config
     */
    public function __construct(private Config $config)
    {
        $this->cache = app('cache');
        $this->client = $this->createClient();
    }

    /** {@inheritdoc} */
    public function find(array $filterParams, array $paginationParams): FinderResult
    {
        if (!array_key_exists('providedIn', $filterParams)) {
            throw new InvalidArgumentException('filter params must contain providedIn');
        }

        $filterKeys = [
            'providedIn',
            'category',
            'isCoaching',
            'isHospitalized',
            'isLongHospitalized',
            'isSecondary',
            'q',
            'serviceCodes',
            'timeframe',
        ];

        $options = [
            'query' => Map::from($filterParams)
                ->filter(fn (mixed $_, string $k): bool => in_array($k, $filterKeys, true))
                ->mapValues(fn (mixed $x): mixed => $this->convert($x))
                ->toAssoc(),
        ];
        $url = $this->config->get('zinger.service_code_api.dws_12_url');
        $response = $this->client->get($url, $options);
        $json = $response->getBody()->getContents();
        $array = Json::decode($json, true);
        $entries = Seq::fromArray($array)->map(fn (array $x): DomainEntry => DomainEntry::create([
            'id' => null,
            'dwsVisitingCareForPwsdDictionaryId' => null,
            'serviceCode' => ServiceCode::fromString($x['serviceCode']),
            'category' => DwsServiceCodeCategory::from($x['category']),
            'timeframe' => Timeframe::from($x['timeframe']),
            'duration' => IntRange::create($x['duration']),
            'createdAt' => null,
            'updatedAt' => null,
        ] + $x));

        return $this->buildFinderResult($entries, $paginationParams, 'serviceCode');
    }

    /** {@inheritdoc} */
    public function findByCategory(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): DomainEntry {
        return $this->findByCategoryOption($providedIn, $category)->getOrElse(function () use ($category): void {
            throw new SetupException("DwsVisitingCareForPwsdDictionaryEntry(category = {$category}) not found");
        });
    }

    /** {@inheritdoc} */
    public function findByCategoryOption(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): Option {
        $filterParams = [
            'providedIn' => $providedIn,
            'category' => $category,
        ];
        $paginationParams = [
            'all' => true,
            'itemsPerPage' => 1,
            'sortBy' => 'id',
        ];
        return $this->find($filterParams, $paginationParams)->list->headOption();
    }

    /** {@inheritdoc} */
    public function cursor(array $filterParams, array $orderParams): never
    {
        throw new LogicException('Do not call DwsVisitingCareForPwsdDictionaryEntryFinder::cursor()');
    }

    /**
     * 変換する.
     *
     * @param mixed $values
     * @return array|bool|int|string
     */
    private function convert(mixed $values): bool|int|string|array
    {
        return match (true) {
            ($values instanceof Enum) => $values->value(),
            ($values instanceof Carbon) => $values->format('Y-m'),
            // TODO: これは予期せぬ変換をしてバグをうみそう
            // TODO: このメソッドを親クラスにおいて子のほうでServiceCodeの場合はtoStringして親クラスの実装を呼ぶような実装のほうがよさそう。
            ($values instanceof Model) => $values->toString(),
            is_array($values) => Seq::fromArray($values)->map(fn (mixed $x): mixed => $this->convert($x))->toArray(),
            is_bool($values),
            is_int($values),
            is_string($values) => $values,
            default => throw new InvalidArgumentException("filter params contain unsupported type: {$values}")
        };
    }

    /**
     * HTTP クライアントを生成する.
     *
     * @return \GuzzleHttp\Client
     */
    private function createClient(): HttpClient
    {
        $retry = Middleware::retry(function ($retries, $request, $response, $exception) {
            if ($retries >= 3) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response && $response->getStatusCode() !== '200') {
                return true; // リトライさせる場合はtrueを返す
            }

            return false;
        });
        $stack = HandlerStack::create();
        $stack->push($retry);
        $stack->push(
            new CacheMiddleware(
                new PrivateCacheStrategy(
                    new LaravelCacheStorage(
                        $this->cache->store('redis')
                    )
                )
            ),
            'cache'
        );
        return new HttpClient(['handler' => $stack]);
    }
}
