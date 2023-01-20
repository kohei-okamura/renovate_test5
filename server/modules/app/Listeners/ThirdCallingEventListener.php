<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use Domain\Calling\ThirdCallingEvent;
use Domain\Config\Config;
use Domain\Tel\TelGateway;

/**
 * 出勤確認第三通知イベントリスナー.
 */
final class ThirdCallingEventListener
{
    private Config $config;
    private TelGateway $gateway;

    /**
     * constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \Domain\Tel\TelGateway $gateway
     */
    public function __construct(Config $config, TelGateway $gateway)
    {
        $this->config = $config;
        $this->gateway = $gateway;
    }

    /**
     * handler.
     *
     * @param \Domain\Calling\ThirdCallingEvent $event
     */
    public function handle(ThirdCallingEvent $event): void
    {
        $audioUri = $this->config->get('zinger.staff_attendance_confirmation.third.audio_uri');

        $this->gateway->call($audioUri, $event->staff()->tel);
    }
}
