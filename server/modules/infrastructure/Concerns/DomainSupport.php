<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Concerns;

use Domain\ModelCompat;
use Illuminate\Support\Str;
use Lib\Arrays;

/**
 * ドメインモデル関連の処理.
 *
 * @mixin \Eloquent
 */
trait DomainSupport
{
    /**
     * キーを指定してドメインオブジェクト用の属性値の配列に変換する.
     *
     * @param array $keys
     * @return array|mixed[]
     */
    public function toDomainAttributes(array $keys): array
    {
        return Arrays::generate(function () use ($keys) {
            foreach ($keys as $key) {
                yield Str::camel($key) => $this->getAttributeValue($key);
            }
        });
    }

    /**
     * ドメインオブジェクト用の値の配列に変換する.
     *
     * @return array|mixed[]
     */
    public function toDomainValues(): array
    {
        $keys = array_keys($this->attributes);
        return $this->toDomainAttributes($keys);
    }

    /**
     * ドメインオブジェクトから値を取得する.
     *
     * @param \Domain\Model $domain
     * @param array|string[] $keys
     * @return array
     */
    protected static function getDomainValues(ModelCompat $domain, array $keys): array
    {
        return Arrays::generate(function () use ($domain, $keys) {
            foreach ($keys as $key) {
                $camelKey = Str::camel($key);
                yield $key => $domain->get($camelKey);
            }
        });
    }
}
