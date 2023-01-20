<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib\Exceptions;

/**
 * 例外: アクセスを許可されていない.(有効期限切れ).
 */
class ForbiddenException extends RuntimeException
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link https://php.net/manual/en/exception.construct.php
     * @param string $message The Exception message to throw. ログ出力に使用するため必須.
     * @param int $code [optional] The Exception code
     * @param throwable $previous [optional] The previous throwable used for the exception chaining
     */
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
