<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit;

/**
 * Fixtures Interface.
 */
interface Fixtures
{
    /**
     * フィクスチャで利用しないID.
     */
    public const NOT_EXISTING_ID = 99;

    /**
     * フィクスチャで利用しないTOKEN.
     */
    public const NOT_EXISTING_TOKEN = 'not_existing_token';

    /**
     * フィクスチャで使用しないEnum値
     */
    public const INVALID_ENUM_VALUE = -9876;
}
