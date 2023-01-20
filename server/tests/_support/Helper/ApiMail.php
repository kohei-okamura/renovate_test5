<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Helper;

use Codeception\Module;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;

/**
 * API Test Helper Class for Mail.
 */
class ApiMail extends Module
{
    /**
     * 受信済みメールのクリア.
     */
    public function clearReceivedMail(): void
    {
        $this->initClient()->delete('/email/all');
    }

    /**
     * 受信済みメールの一覧取得.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAllReceivedMail(): ResponseInterface
    {
        return $this->initClient()->get('/email');
    }

    /**
     * 受信済みメールの取得.
     *
     * @param string $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getReceivedMail(string $id): ResponseInterface
    {
        return $this->initClient()->get("/email/{$id}");
    }

    /**
     * 取得メール件数のアサート.
     *
     * @param int $expect
     */
    public function seeReceivedMailCount(int $expect): void
    {
        $mails = $this->grabMailList();
        $this->assertCount($expect, $mails);
    }

    public function seeReceivedMail(string $id, array $expects)
    {
        $mail = $this->grabMail($id);
        foreach ($expects as $key => $expect) {
            $actual = Arr::get($mail, $key, null);
            $this->assertSame($expect, $actual);
        }
    }

    public function grabMailList(): array
    {
        return json_decode($this->getAllReceivedMail()->getBody()->getContents(), true);
    }

    public function grabMail(string $id): array
    {
        return json_decode($this->getReceivedMail($id)->getBody()->getContents(), true);
    }

    /**
     * HTTP Client の初期化.
     *
     * @return \GuzzleHttp\Client
     */
    private function initClient(): Client
    {
        return new Client(['base_uri' => config('mail.test_api_url')]);
    }
}
