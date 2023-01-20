<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contracts;

/**
 * Token Maker Interface.
 */
interface TokenMaker
{
    public const DEFAULT_TOKEN_LENGTH = 60;

    /**
     * 指定した文字列長のトークンを生成する.
     *
     * @param int $length
     * @return string
     */
    public function make(int $length): string;
}
