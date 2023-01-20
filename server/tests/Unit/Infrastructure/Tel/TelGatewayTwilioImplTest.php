<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Tel;

use Infrastructure\Tel\TelGatewayTwilioImpl;
use Lib\Exceptions\NetworkIOException;
use Mockery;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TwilioClientMixin;
use Tests\Unit\Test;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\CallInstance;

/**
 * {@link \Infrastructure\Tel\TelGatewayTwilioImpl} Test.
 */
class TelGatewayTwilioImplTest extends Test
{
    use ConfigMixin;
    use MockeryMixin;
    use TwilioClientMixin;
    use UnitSupport;

    private const FROM_TEL_NUMBER = '12345';
    private const AUDIO_URL = 'audio_url';

    private TelGatewayTwilioImpl $impl;
    /** @var \Mockery\MockInterface|\Twilio\Rest\Api\V2010\Account\CallInstance */
    private $twilioCall;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (TelGatewayTwilioImplTest $self): void {
            $self->twilioCall = Mockery::mock(CallInstance::class);

            $self->config
                ->allows('get')
                ->with('zinger.twilio.from_tel_number')
                ->andReturn(self::FROM_TEL_NUMBER);
            $self->twilioClient->calls
                ->allows('create')
                ->andReturn($self->twilioCall)
                ->byDefault();

            $self->impl = app(TelGatewayTwilioImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_call(): void
    {
        $this->should('call twilio API via Client', function (): void {
            /** @var Mockery\MockInterface $calls */
            $calls = $this->twilioClient->calls;
            $calls->expects('create')
                ->with('+81123456789', self::FROM_TEL_NUMBER, equalTo(['url' => self::AUDIO_URL]))
                ->andReturn($this->twilioCall);

            $this->impl->call(self::AUDIO_URL, '0123456789');
        });
        $this->should('throw NetworkIOException when Twilio API Error', function (): void {
            /** @var Mockery\MockInterface $calls */
            $calls = $this->twilioClient->calls;
            $calls->expects('create')
                ->andThrow(TwilioException::class);

            $this->assertThrows(
                NetworkIOException::class,
                function (): void {
                    $this->impl->call(self::AUDIO_URL, '0123456789');
                }
            );
        });
    }
}
