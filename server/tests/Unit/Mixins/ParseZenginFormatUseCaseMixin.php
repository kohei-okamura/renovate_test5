<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\ParseZenginFormatUseCase;

/**
 * {@link \UseCase\UserBilling\ParseZenginFormatUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ParseZenginFormatUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\ParseZenginFormatUseCase
     */
    protected $parseZenginFormatUseCase;

    /**
     * {@link \UseCase\UserBilling\ParseZenginFormatUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinParseZenginFormatUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ParseZenginFormatUseCase::class,
                fn () => $self->parseZenginFormatUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->parseZenginFormatUseCase = Mockery::mock(
                ParseZenginFormatUseCase::class
            );
        });
    }
}
