<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Sms;

use Domain\Calling\StaffAttendanceSmsMessage;
use Infrastructure\Sms\SmsGatewayTwilioImpl;
use Lib\Exceptions\ExternalApiException;
use Lib\Exceptions\NetworkIOException;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TwilioClientMixin;
use Tests\Unit\Test;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

/**
 * {@link \Infrastructure\Sms\SmsGatewayTwilioImpl} Test.
 */
class SmsGatewayTwilioImplTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use TwilioClientMixin;
    use UnitSupport;

    private const FROM_SMS_NUMBER = 12345;
    private const MINUTES = 70;
    private const URL = 'http://test.example.com/';

    private SmsGatewayTwilioImpl $impl;
    /** @var \Mockery\MockInterface|\Twilio\Rest\Api\V2010\Account\MessageInstance */
    private $twilioMessage;
    private StaffAttendanceSmsMessage $smsMessage;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SmsGatewayTwilioImplTest $self): void {
            $self->twilioMessage = Mockery::mock(MessageInstance::class);
            $self->twilioMessage->status = 'sent';

            $self->config
                ->allows('get')
                ->with('zinger.twilio.from_sms_number')
                ->andReturn(self::FROM_SMS_NUMBER);
            $self->twilioClient->messages
                ->allows('create')
                ->andReturn($self->twilioMessage)
                ->byDefault();

            $self->smsMessage = StaffAttendanceSmsMessage::create([
                'minutes' => self::MINUTES,
                'shift' => $self->examples->shifts[0],
                'url' => self::URL,
            ]);

            $self->impl = app(SmsGatewayTwilioImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        $this->should('use Twilio client', function (): void {
            $minutes = self::MINUTES;
            $url = self::URL;
            $date = $this->smsMessage->shift->schedule->date->format('n/j');
            $this->twilioClient->messages
                ->expects('create')
                ->with('+81123456789', equalTo([
                    'body' => "【出確{$date}】サービス{$minutes}分前!出勤なら即押→ {$url} 土屋訪問",
                    'from' => self::FROM_SMS_NUMBER,
                ]))
                ->andReturn($this->twilioMessage);

            $this->impl->send($this->smsMessage, '0123456789');
        });
        $this->should('throw NetworkIOException when Twilio API Error', function (): void {
            $message = $this->twilioClient->messages;
            assert($message instanceof Mockery\MockInterface);
            $message->expects('create')->andThrow(TwilioException::class);

            $this->assertThrows(
                NetworkIOException::class,
                function (): void {
                    $this->impl->send($this->smsMessage, $this->examples->staffs[0]->tel);
                }
            );
        });
        $this->should('throw ExternalApiException when Twilio API status is error', function (): void {
            $this->twilioMessage->status = 'failed';
            $this->twilioMessage->errorMessage = 'errorMessage';
            $this->twilioMessage->errorCode = 12345;
            $this->twilioClient->messages
                ->expects('create')
                ->andReturn($this->twilioMessage);

            $this->assertThrows(
                ExternalApiException::class,
                function (): void {
                    $this->impl->send($this->smsMessage, $this->examples->staffs[0]->tel);
                }
            );
        });
    }
}
