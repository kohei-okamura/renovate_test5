<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Illuminate\Support\Str;
use UseCase\Contracts\TokenMaker;

/**
 * Token Maker Implementation.
 */
final class TokenMakerImpl implements TokenMaker
{
    /** {@inheritdoc} */
    public function make(int $length): string
    {
        return Str::random($length);
    }
}
