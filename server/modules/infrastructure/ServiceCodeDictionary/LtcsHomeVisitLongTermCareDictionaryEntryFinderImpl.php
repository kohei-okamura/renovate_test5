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
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsCalcCycle;
use Domain\ServiceCodeDictionary\LtcsCalcExtraScore;
use Domain\ServiceCodeDictionary\LtcsCalcScore;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Domain\ServiceCodeDictionary\LtcsCompositionType;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry as DomainEntry;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
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
use Lib\Json;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\BuildFinderResultHolder;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder} 実装.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryFinderImpl implements LtcsHomeVisitLongTermCareDictionaryEntryFinder
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
            'dictionaryId',
            'headcount',
            'houseworkMinutes',
            'physicalMinutes',
            'q',
            'serviceCodes',
            'specifiedOfficeAddition',
            'timeframe',
            'totalMinutes',
        ];

        $options = [
            'query' => Map::from($filterParams)
                ->filter(fn (mixed $_, string $k): bool => in_array($k, $filterKeys, true))
                ->mapValues(fn (mixed $x): mixed => $this->convert($x))
                ->toAssoc(),
        ];
        $url = $this->config->get('zinger.service_code_api.ltcs_url');
        $response = $this->client->get($url, $options);
        $json = $response->getBody()->getContents();
        $array = Json::decode($json, true);
        $entries = Seq::fromArray($array)->map(fn (array $x): DomainEntry => DomainEntry::create([
            'id' => null,
            'dictionaryId' => null,
            'serviceCode' => ServiceCode::fromString($x['serviceCode']),
            'category' => LtcsServiceCodeCategory::from($x['category']),
            'compositionType' => LtcsCompositionType::from($x['compositionType']),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($x['specifiedOfficeAddition']),
            'noteRequirement' => LtcsNoteRequirement::from($x['noteRequirement']),
            'score' => LtcsCalcScore::create([
                'calcCycle' => LtcsCalcCycle::from($x['score']['calcCycle']),
                'calcType' => LtcsCalcType::from($x['score']['calcType']),
                'value' => $x['score']['value'],
            ]),
            'extraScore' => LtcsCalcExtraScore::create($x['extraScore']),
            'timeframe' => Timeframe::from($x['timeframe']),
            'totalMinutes' => IntRange::create($x['totalMinutes']),
            'physicalMinutes' => IntRange::create($x['physicalMinutes']),
            'houseworkMinutes' => IntRange::create($x['houseworkMinutes']),
            'createdAt' => null,
            'updatedAt' => null,
        ] + $x));

        return $this->buildFinderResult($entries, $paginationParams, 'serviceCode');
    }

    /** {@inheritdoc} */
    public function cursor(array $filterParams, array $orderParams): never
    {
        throw new LogicException('Do not call LtcsHomeVisitLongTermCareDictionaryEntryFinder::cursor()');
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
