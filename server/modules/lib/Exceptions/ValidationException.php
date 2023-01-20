<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib\Exceptions;

/**
 * 例外: バリデーションエラー.
 */
class ValidationException extends \RuntimeException
{
    private iterable $errors;

    /**
     * Constructor.
     *
     * @param iterable $errors
     */
    public function __construct(iterable $errors)
    {
        parent::__construct();
        $this->errors = $errors;
    }

    public function getErrors(): iterable
    {
        return $this->errors;
    }
}
