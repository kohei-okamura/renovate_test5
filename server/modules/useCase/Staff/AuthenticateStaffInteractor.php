<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Staff\Staff;
use Domain\Staff\StaffLoggedInEvent;
use Lib\Logging;
use ScalikePHP\Option;

/**
 * Authenticate Staff interactor.
 */
class AuthenticateStaffInteractor implements AuthenticateStaffUseCase
{
    use Logging;

    private IdentifyStaffByEmailUseCase $lookupUseCase;
    private BuildAuthResponseUseCase $buildAuthResponseUseCase;
    private EventDispatcher $dispatcher;

    /**
     * Constructor.
     *
     * @param \UseCase\Staff\BuildAuthResponseUseCase $buildAuthResponseUseCase
     * @param \Domain\Event\EventDispatcher $dispatcher
     * @param \UseCase\Staff\IdentifyStaffByEmailUseCase $lookupUseCase
     */
    public function __construct(
        BuildAuthResponseUseCase $buildAuthResponseUseCase,
        EventDispatcher $dispatcher,
        IdentifyStaffByEmailUseCase $lookupUseCase
    ) {
        $this->buildAuthResponseUseCase = $buildAuthResponseUseCase;
        $this->dispatcher = $dispatcher;
        $this->lookupUseCase = $lookupUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $email, string $password, bool $rememberMe): Option
    {
        return $this->lookupUseCase->handle($context, $email)
            ->filter(fn (Staff $staff): bool => $staff->isVerified)
            ->filter(fn (Staff $staff): bool => $staff->password->check($password))
            ->map(function (Staff $staff) use ($context, $rememberMe) {
                $this->logger()->info('スタッフがログインしました', ['staffId' => $staff->id] + $context->logContext());
                $event = new StaffLoggedInEvent($context, $staff, $rememberMe);
                $this->dispatcher->dispatch($event);
                return $this->buildAuthResponseUseCase->handle($context, $staff);
            });
    }
}
