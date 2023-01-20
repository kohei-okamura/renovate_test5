<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\FirstCallingEventListener;
use Domain\Calling\FirstCallingEvent;
use Illuminate\Mail\Mailable;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MailerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Listeners\FirstCallingEventListener} Test.
 */
class FirstCallingEventListenerTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MailerMixin;
    use MockeryMixin;
    use UnitSupport;

    private const URL = 'http://url.test/';

    private FirstCallingEventListener $listener;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FirstCallingEventListenerTest $self): void {
            $self->listener = app(FirstCallingEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('mail to build mail by Builder', function (): void {
            $this->mailer
                ->expects('send')
                ->andReturnUsing(function (Mailable $mail): void {
                    $this->assertSame('本日のシフトが2時間後に開始されます', $mail->subject);
                    $this->assertMailViewExists($mail);
                    $this->assertSame($this->examples->staffs[0]->email, $mail->to[0]['address']);
                    $this->assertEquals(
                        [
                            'expiredAt' => $this->examples->callings[0]->expiredAt->format('H:i'),
                            'staff' => $this->examples->staffs[0],
                            'url' => self::URL,
                            'loginUrl' => "https://{$this->context->organization->code}.careid.jp/",
                        ],
                        $mail->viewData
                    );
                    $this->assertSame($this->examples->callings[0]->expiredAt->format('H:i'), $mail->viewData['expiredAt']);
                    $this->assertModelStrictEquals($this->examples->staffs[0], $mail->viewData['staff']);
                    $this->assertSame(self::URL, $mail->viewData['url']);
                    $this->assertSame("https://{$this->context->organization->code}.careid.jp/", $mail->viewData['loginUrl']);
                });

            $this->listener
                ->handle(new FirstCallingEvent(
                    $this->context,
                    $this->examples->callings[0],
                    $this->examples->staffs[0],
                    self::URL
                ));
        });
    }
}
