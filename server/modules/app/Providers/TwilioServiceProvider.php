<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use Domain\Config\Config;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

/**
 * Twilio Service Provider.
 *
 * @codeCoverageIgnore リクエスト受信〜APPに来るまでの処理なのでUnitTest除外
 */
class TwilioServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->app->make(Config::class);
        $apiKeySid = $config->get('zinger.twilio.api_key_sid');
        $apiKeySecret = $config->get('zinger.twilio.api_key_secret');
        $accountSid = $config->get('zinger.twilio.account_sid');
        $this->app->bind(Client::class, fn () => new Client($apiKeySid, $apiKeySecret, $accountSid));
    }
}
