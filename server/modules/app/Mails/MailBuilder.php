<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Illuminate\Mail\Mailable;

/**
 * MailBuilder Interface.
 */
interface MailBuilder
{
    /**
     * Build the mail.
     *
     * @return \Illuminate\Mail\Mailable
     */
    public function build(): Mailable;
}
