<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Event\EventDispatcher;
use Mockery;

/**
 * EventDispatcher Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EventDispatcherMixin
{
    /**
     * @var \Domain\Event\EventDispatcher|\Mockery\MockInterface
     */
    protected $eventDispatcher;

    public static function mixinEventDispatcher(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EventDispatcher::class, fn () => $self->eventDispatcher);
        });
        static::beforeEachSpec(function ($self): void {
            $self->eventDispatcher = Mockery::mock(EventDispatcher::class);
        });
    }
}
