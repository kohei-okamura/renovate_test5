<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Event;

/**
 * Event Dispatcher Interface.
 */
interface EventDispatcher
{
    /**
     * Dispatch an {@link \Domain\Event\Event}.
     *
     * @param \Domain\Event\Event $event
     * @return void
     */
    public function dispatch(Event $event): void;
}
