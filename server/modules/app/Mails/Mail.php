<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Illuminate\Mail\Mailable;

/**
 * Mail.
 */
final class Mail extends Mailable
{
    /**
     * Create a new instance.
     *
     * @return \App\Mails\Mail
     */
    public static function make()
    {
        return new static();
    }

    /**
     * Build the mail.
     *
     * @return void
     * @codeCoverageIgnore 使われていないメソッド
     */
    public function build(): void
    {
        // noop
    }
}
