<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ShoutUrl;

/**
 * 短縮URL生成ゲートウェイ.
 */
interface UrlShortenerGateway
{
    /**
     * handle.
     *
     * @param string $originalUrl オリジナルURL
     * @return string short Url
     */
    public function handle(string $originalUrl): string;
}
