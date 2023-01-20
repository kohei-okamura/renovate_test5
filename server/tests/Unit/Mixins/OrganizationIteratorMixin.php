<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use App\Console\OrganizationIterator;
use Mockery;

/**
 * {@link \App\Console\OrganizationIterator} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OrganizationIteratorMixin
{
    /**
     * @var \App\Console\OrganizationIterator|\Mockery\MockInterface
     */
    protected $organizationIterator;

    /**
     * {@link \App\Console\OrganizationIterator} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOrganizationIterator(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OrganizationIterator::class, fn () => $self->organizationIterator);
        });
        static::beforeEachSpec(function ($self): void {
            $self->organizationIterator = Mockery::mock(OrganizationIterator::class);
        });
    }
}
