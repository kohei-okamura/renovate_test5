<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Sms;

use Domain\Config\Config;
use Domain\Sms\SmsGateway;
use Domain\Sms\SmsMessage;
use Lib\Exceptions\ExternalApiException;
use Lib\Exceptions\NetworkIOException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client as TwilioClient;

/**
 * SMS送信 Twilio実装.
 */
class SmsGatewayTwilioImpl implements SmsGateway
{
    private const TWILIO_STATUS_FAILED = 'failed';
    private const TWILIO_STATUS_UNDELIVERED = 'undelivered';

    private TwilioClient $client;
    private Config $config;

    /**
     * constructor.
     * @param \Domain\Config\Config $config
     * @param \Twilio\Rest\Client $client
     */
    public function __construct(Config $config, TwilioClient $client)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function send(SmsMessage $smsMessage, string $destination): void
    {
        $canonicalNumber = preg_replace('/^0/', '+81', str_replace('-', '', $destination));
        $body = $smsMessage->getMessage();
        $from = $this->config->get('zinger.twilio.from_sms_number');
        try {
            $message = $this->client->messages
                ->create($canonicalNumber, compact('body', 'from'));
        } catch (TwilioException $e) {
            $log = $e->getMessage() . \PHP_EOL . $e->getTraceAsString();
            throw new NetworkIOException('Twilio API へのリクエストが失敗しました: ' . $log, $e->getCode(), $e);
        }

        $status = $message->status;
        if ($status === self::TWILIO_STATUS_FAILED || $status === self::TWILIO_STATUS_UNDELIVERED) {
            throw new ExternalApiException(
                'Twilio API から OK 以外のレスポンスを受け取りました: ' . $message->errorMessage,
                $message->errorCode,
            );
        }
    }
}
