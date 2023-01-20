<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Events;

use Domain\Event\Event;
use Domain\Event\EventDispatcher as DomainEventDispatcher;
use Illuminate\Events\Dispatcher;

/**
 * Event Dispatcher Implementation.
 *
 * @codeCoverageIgnore モジュール間の連携機能なのでUnitTestでは除外
 */
final class EventDispatcher implements DomainEventDispatcher
{
    private Dispatcher $dispatcher;

    /**
     * {@link \App\Events\EventDispatcher} constructor.
     */
    public function __construct()
    {
        $this->dispatcher = app('events');
    }

    /** {@inheritdoc} */
    public function dispatch(Event $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
