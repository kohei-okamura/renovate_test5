<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use Twilio\Rest\Api\V2010\Account\CallList;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Api\V2010\AccountInstance;
use Twilio\Rest\Client;

/**
 * TwilioClient Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait TwilioClientMixin
{
    /**
     * @var \Mockery\MockInterface|\Twilio\Rest\Client
     */
    protected $twilioClient;

    /**
     * @var \Mockery\MockInterface|\Twilio\Rest\Api\V2010\Account\MessageList
     */
    protected $messages;

    /**
     * @var \Mockery\MockInterface|\Twilio\Rest\Api\V2010\Account\CallList
     */
    protected $calls;

    /**
     * TwilioClient に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinTwilioClientMixin(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(Client::class, fn () => $self->twilioClient);
        });
        static::beforeEachSpec(function ($self): void {
            $self->twilioClient = Mockery::mock(Client::class);
            $self->twilioClient->messages = Mockery::mock(MessageList::class);
            $self->twilioClient->account = Mockery::mock(AccountInstance::class);
            $self->twilioClient->calls = Mockery::mock(CallList::class);
        });
    }
}
