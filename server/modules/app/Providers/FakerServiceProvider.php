<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;
use Tests\Unit\Faker\FakeAddrProvider;
use Tests\Unit\Faker\FakeDomainProvider;
use Tests\Unit\Faker\FakeEmailAddressProvider;
use Tests\Unit\Faker\FakeNameProvider;
use Tests\Unit\Faker\FakeOfficeNameProvider;

/**
 * Faker Service Provider.
 *
 * @codeCoverageIgnore テストデータ用なのでCoverage除外
 */
final class FakerServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     *
     * Faker のロケールを日本語に変更する.
     */
    public function register(): void
    {
        $this->app->singleton(Generator::class, function (): Generator {
            $faker = Factory::create('ja_JP');
            $faker->addProvider(new FakeAddrProvider($faker));
            $faker->addProvider(new FakeDomainProvider($faker));
            $faker->addProvider(new FakeEmailAddressProvider($faker));
            $faker->addProvider(new FakeNameProvider($faker));
            $faker->addProvider(new FakeOfficeNameProvider($faker));
            return $faker;
        });
    }
}
