<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Enum;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lib\Arrays;
use Lib\Exceptions\InvalidArgumentException;

/**
 * Find Request.
 */
abstract class FindRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 検索条件に使用する入力値.
     *
     * @param array $replaceKeyPairs
     * @return array
     */
    public function filterParams(array $replaceKeyPairs = []): array
    {
        return Arrays::generate(function () use ($replaceKeyPairs): iterable {
            $enumParams = $this->enumParams();
            foreach (Arr::only($this->input(), $this->filterKeys()) as $key => $value) {
                $buildKey = array_key_exists($key, $replaceKeyPairs) ? $replaceKeyPairs[$key] : $key;
                if (in_array($key, $this->boolParams(), true)) {
                    yield $buildKey => static::toBool($value);
                } elseif (in_array($key, $this->carbonParams(), true)) {
                    yield $buildKey => static::toCarbon($value);
                } elseif (in_array($key, $this->integerParams(), true)) {
                    yield $buildKey => static::toInt($value);
                } elseif (array_key_exists($key, $enumParams)) {
                    yield $buildKey => static::toEnum($value, $enumParams[$key]);
                } else {
                    yield $buildKey => $value;
                }
            }
        });
    }

    /**
     * ページネーションに使用する入力値.
     *
     * @return array
     */
    public function paginationParams(): array
    {
        $input = $this->input();
        return Arr::only($input, ['sortBy'])
            + array_map(fn (string $in): bool => static::toBool($in), Arr::only($input, ['all', 'desc']))
            + array_map(fn (string $in): int => (int)$in, Arr::only($input, ['itemsPerPage', 'page']));
    }

    /**
     * 論理値パラメータの指定.
     *
     * @return array
     */
    protected function boolParams(): array
    {
        return [];
    }

    /**
     * 日付時刻パラメータの指定.
     *
     * @return array
     */
    protected function carbonParams(): array
    {
        return [];
    }

    /**
     * 数値パラメータの指定.
     *
     * @return array
     */
    protected function integerParams(): array
    {
        return [];
    }

    /**
     * 列挙型パラメータの指定.
     * パラメータの名前を key, 変換したい Enum を値にした連想配列を返す
     * e.g. ['purpose' => Purpose::class]
     *
     * @return array
     */
    protected function enumParams(): array
    {
        return [];
    }

    /**
     * リクエストパラメータをbool型に変換する.
     *
     * @param mixed $value
     * @return bool
     */
    protected static function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        } elseif (is_numeric($value)) {
            return (bool)$value;
        } elseif (is_string($value)) {
            return Str::lower($value) === 'true';
        } else {
            // bool, numeric, string 以外が来ることはないはずだが念の為
            return (bool)$value; // @codeCoverageIgnore
        }
    }

    /**
     * リクエストパラメータをCarbon型に変換する.
     *
     * @param $value
     * @return null|\Domain\Common\Carbon
     */
    protected static function toCarbon($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }
        return Carbon::parse($value);
    }

    /**
     * リクエストパラメータをint に変換する.
     *
     * @param $value
     * @return int
     */
    protected static function toInt($value): int
    {
        return (int)$value;
    }

    /**
     * リクエストパラメータを Enum に変換する.
     *
     * @param array|int|string $value
     * @param string $className
     * @return \Domain\Enum|\Domain\Enum[]
     */
    protected static function toEnum(int|string|array $value, string $className): Enum|array
    {
        if (empty($className)) {
            throw new InvalidArgumentException('$className must not be empty.');
        }
        return match (true) {
            is_array($value) => array_map(fn (int|string|array $x): Enum|array => static::toEnum($x, $className), $value),
            default => call_user_func([$className, 'from'], is_numeric($value) ? (int)$value : $value)
        };
    }

    /**
     * 検索条件に使用する入力値のキー
     *
     * @return array
     */
    abstract protected function filterKeys(): array;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'all' => ['boolean_ext'],
            'desc' => ['boolean_ext'],
            'itemsPerPage' => ['integer'],
            'page' => ['integer'],
        ];
    }
}
