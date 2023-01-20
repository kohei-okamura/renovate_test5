<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Faker;

use Faker\Generator as FakerGenerator;

/**
 * Faker 生成処理.
 */
final class Faker
{
    /**
     * {@link \Faker\Generator} のインスタンスを生成する.
     *
     * @param int $seed
     * @return \Faker\Generator
     */
    public static function make(int $seed): FakerGenerator
    {
        /** @var \Faker\Generator $faker */
        $faker = app(FakerGenerator::class);
        mt_srand($seed, \MT_RAND_MT19937);
        return $faker;
    }

    /**
     * {@link \Faker\Generator} を初期化する.
     *
     * @param int $seed
     * @return void
     */
    public static function seed(int $seed): void
    {
        mt_srand($seed, \MT_RAND_MT19937);
    }
}
