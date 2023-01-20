<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Domain\Staff\StaffLoggedInEvent;
use Illuminate\Cookie\CookieJar;
use Lib\Json;
use UseCase\Staff\CreateStaffRememberTokenUseCase;

/**
 * スタッフログインイベントリスナー.
 */
final class StaffLoggedInEventListener
{
    private Config $config;
    private CookieJar $cookie;
    private CreateStaffRememberTokenUseCase $createRememberTokenUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Staff\CreateStaffRememberTokenUseCase $createRememberTokenUseCase
     */
    public function __construct(Config $config, CreateStaffRememberTokenUseCase $createRememberTokenUseCase)
    {
        $this->config = $config;
        $this->cookie = app('cookie');
        $this->createRememberTokenUseCase = $createRememberTokenUseCase;
    }

    /**
     * @param \Domain\Staff\StaffLoggedInEvent $event
     */
    public function handle(StaffLoggedInEvent $event): void
    {
        if ($event->rememberMe()) {
            $rememberToken = $this->createRememberTokenUseCase->handle($event->context(), $event->staff());
            $value = Json::encode([
                'id' => $rememberToken->id,
                'staffId' => $rememberToken->staffId,
                'token' => $rememberToken->token,
            ]);
            $days = $this->config->get('zinger.remember_token.lifetime_days');
            $name = $this->config->get('zinger.remember_token.cookie_name');
            $lifetime = Carbon::now()->addDays($days)->diffInMinutes();
            $this->cookie->queue($name, $value, $lifetime);
        }
    }
}
