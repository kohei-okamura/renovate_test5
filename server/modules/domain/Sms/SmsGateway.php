<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Sms;

/**
 * SMS送信ゲートウェイ.
 */
interface SmsGateway
{
    /**
     * SMS送信.
     *
     * @param \Domain\Sms\SmsMessage $message
     * @param string $destination 宛先電話番号
     */
    public function send(SmsMessage $message, string $destination): void;
}
