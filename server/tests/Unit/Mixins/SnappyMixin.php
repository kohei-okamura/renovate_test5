<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Barryvdh\Snappy\PdfWrapper;
use Mockery;

/**
 * {@link \Barryvdh\Snappy\PdfWrapper} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SnappyMixin
{
    /**
     * @var \Barryvdh\Snappy\PdfWrapper|\Mockery\MockInterface
     */
    protected $snappy;

    /**
     * Snappy に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingBundleFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind('snappy.pdf.wrapper', fn () => $self->snappy);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->snappy = Mockery::mock(PdfWrapper::class);
        });
    }
}
