<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use Domain\Config\Config;
use Domain\Staff\StaffLoggedOutEvent;
use Illuminate\Cookie\CookieJar;
use UseCase\Staff\RemoveStaffRememberTokenUseCase;

/**
 * Event listener for StaffLoggedOutEvent.
 */
final class StaffLoggedOutEventListener
{
    private RemoveStaffRememberTokenUseCase $removeRememberTokenUseCase;
    private Config $config;
    private CookieJar $cookie;

    /**
     * Constructor.
     *
     * @param RemoveStaffRememberTokenUseCase $removeRememberTokenUseCase
     * @param Config $config
     */
    public function __construct(RemoveStaffRememberTokenUseCase $removeRememberTokenUseCase, Config $config)
    {
        $this->config = $config;
        $this->cookie = app('cookie');
        $this->removeRememberTokenUseCase = $removeRememberTokenUseCase;
    }

    /**
     * @param \Domain\Staff\StaffLoggedOutEvent $event
     */
    public function handle(StaffLoggedOutEvent $event): void
    {
        $event->rememberTokenId()->each(function (int $id) use ($event): void {
            $this->removeRememberTokenUseCase->handle($event->context(), $id);
            $name = $this->config->get('zinger.remember_token.cookie_name');
            $this->cookie->forget($name);
        });
    }
}
