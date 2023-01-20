<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Pdf\PdfCreator;
use Mockery;

/**
 * {@link \Domain\Pdf\PdfCreator} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait PdfCreatorMixin
{
    /**
     * @var \Domain\Pdf\PdfCreator|\Mockery\MockInterface
     */
    protected PdfCreator $pdfCreator;

    public static function mixinPdfCreator(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(PdfCreator::class, fn () => $self->pdfCreator);
        });
        static::beforeEachSpec(function ($self): void {
            $self->pdfCreator = Mockery::mock(PdfCreator::class);
        });
    }
}
