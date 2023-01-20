<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Arr;
use Lib\LazyField;

/**
 * Fake Domain Provider
 *
 * @property-read string[] $domains
 */
final class FakeDomainProvider extends Base
{
    use LazyField;

    /**
     * インターネットドメインを生成する.
     *
     * @return string
     */
    public function domain(): string
    {
        return Arr::random($this->domains);
    }

    /**
     * @return array
     */
    private function domains(): array
    {
        $g = function () {
            yield 'example.com';
            yield 'example.net';
            yield 'example.org';
            yield 'example.jp';
            yield 'example.co.jp';
            yield 'example.ne.jp';
            for ($i = 9; $i >= 0; --$i) {
                yield "example{$i}.jp";
                yield "example{$i}.co.jp";
                yield "example{$i}.ne.jp";
            }
        };
        return iterator_to_array(call_user_func($g));
    }
}
