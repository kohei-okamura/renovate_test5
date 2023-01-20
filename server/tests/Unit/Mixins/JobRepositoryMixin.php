<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Job\JobRepository;
use Mockery;

/**
 * JobRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait JobRepositoryMixin
{
    /**
     * @var \Domain\Job\JobRepository|\Mockery\MockInterface
     */
    protected $jobRepository;

    /**
     * JobRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinJobRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(JobRepository::class, fn () => $self->jobRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->jobRepository = Mockery::mock(JobRepository::class);
        });
    }
}
