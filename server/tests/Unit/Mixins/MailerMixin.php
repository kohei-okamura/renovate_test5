<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\Mail\Mailer;
use Mockery;

/**
 * Mailer Mixin
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait MailerMixin
{
    /**
     * @var \Illuminate\Mail\Mailer|\Mockery\MockInterface
     */
    protected $mailer;

    /**
     * Mailer に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinMailer(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind('mailer', fn () => $self->mailer);
        });
        static::beforeEachSpec(function ($self): void {
            $self->mailer = Mockery::mock(Mailer::class);
        });
    }
}
