<?php

declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Staff\StaffLoggedOutEvent;
use ScalikePHP\Option;

/**
 * ログアウトユースケース実装.
 */
final class StaffLoggedOutInteractor implements StaffLoggedOutUseCase
{
    private EventDispatcher $eventDispatcher;

    /**
     * Constructor.
     *
     * @param \Domain\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Option $rememberTokenId): void
    {
        $this->eventDispatcher->dispatch(new StaffLoggedOutEvent($context, $rememberTokenId));
    }
}
