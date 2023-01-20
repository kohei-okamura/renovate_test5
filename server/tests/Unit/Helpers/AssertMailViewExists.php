<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Illuminate\Mail\Mailable;

/**
 * 渡されたMailableのviewファイルが存在していることを検査する.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertMailViewExists
{
    protected function assertMailViewExists(Mailable $mail): void
    {
        $view = $mail->textView;
        $bladeFile = 'views/' . str_replace('.', \DIRECTORY_SEPARATOR, $view) . '.blade.php';
        $this->assertFileExists(resource_path($bladeFile), 'Balde not found: ' . $view);
    }
}
