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
 * Pdf Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait PdfMixin
{
    /**
     * @var \Barryvdh\Snappy\PdfWrapper|\Mockery\MockInterface
     */
    protected $pdf;

    /**
     * {@link \Barryvdh\Snappy\PdfWrapper} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinPdf(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind('snappy.pdf.wrapper', fn () => $self->pdf);
        });
        static::beforeEachSpec(function ($self): void {
            $self->pdf = Mockery::mock(PdfWrapper::class);
        });
    }
}
