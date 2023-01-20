<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Tel;

use Domain\Config\Config;
use Domain\Tel\TelGateway;
use Lib\Exceptions\NetworkIOException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

/**
 * TEL発信ゲートウェイ Twilio実装.
 */
class TelGatewayTwilioImpl implements TelGateway
{
    private Client $client;
    private Config $config;

    /**
     * constructor.
     *
     * @param \Twilio\Rest\Client $client
     * @param \Domain\Config\Config $config
     */
    public function __construct(Client $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function call(string $audioFileUri, string $destination): void
    {
        $canonicalNumber = preg_replace('/^0/', '+81', str_replace('-', '', $destination));
        $from = $this->config->get('zinger.twilio.from_tel_number');
        try {
            $this->client->calls
                ->create(
                    $canonicalNumber,
                    $from,
                    ['url' => $audioFileUri],
                );
        } catch (TwilioException $e) {
            $log = $e->getMessage() . \PHP_EOL . $e->getTraceAsString();
            throw new NetworkIOException('Twilio API へのリクエストが失敗しました: ' . $log, $e->getCode(), $e);
        }
    }
}
