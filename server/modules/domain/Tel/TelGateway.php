<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Tel;

/**
 * TEL発信ゲートウェイ.
 */
interface TelGateway
{
    /**
     * 電話をかける.
     *
     * @param string $audioFileUri 自動音声のURL
     * @param string $destination 電話番号
     * @return void
     */
    public function call(string $audioFileUri, string $destination): void;
}
