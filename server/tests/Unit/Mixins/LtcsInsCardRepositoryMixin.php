<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\LtcsInsCard\LtcsInsCardRepository;
use Mockery;

/**
 * LtcsInsCardRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsInsCardRepositoryMixin
{
    /**
     * @var \Domain\LtcsInsCard\LtcsInsCardRepository|\Mockery\MockInterface
     */
    protected $ltcsInsCardRepository;

    /**
     * LtcsInsCardRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsInsCardRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsInsCardRepository::class, fn () => $self->ltcsInsCardRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsInsCardRepository = Mockery::mock(LtcsInsCardRepository::class);
        });
    }
}
