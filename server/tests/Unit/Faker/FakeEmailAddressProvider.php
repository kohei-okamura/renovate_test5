<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Faker;

use Faker\Provider\Base;
use Lib\RandomString;

/**
 * Fake EmailAddress Provider
 */
final class FakeEmailAddressProvider extends Base
{
    /**
     * メールアドレスを生成する.
     *
     * @return string
     */
    public function emailAddress(): string
    {
        $domain = $this->generator->domain();
        $subDomain = RandomString::generate(3, RandomString::ALPHABETS);
        $name = RandomString::generate(8, RandomString::ALPHABETS);
        $suffix = RandomString::generate(4, RandomString::NUMBERS);
        return "{$name}{$suffix}@{$subDomain}.{$domain}";
    }
}
