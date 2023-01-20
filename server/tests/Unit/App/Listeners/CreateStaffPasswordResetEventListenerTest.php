<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\CreateStaffPasswordResetEventListener;
use Domain\Staff\CreateStaffPasswordResetEvent;
use Illuminate\Mail\Mailable;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MailerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * CreateStaffPasswordResetEventListener のテスト
 */
class CreateStaffPasswordResetEventListenerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MailerMixin;
    use MockeryMixin;
    use UnitSupport;

    private CreateStaffPasswordResetEventListener $listener;

    /**
     * セットアップ処理
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateStaffPasswordResetEventListenerTest $self): void {
            $self->listener = app(CreateStaffPasswordResetEventListener::class);
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
                    $this->assertSame('careid アカウントのパスワード再設定を受け付けました', $mail->subject);
                    $this->assertMailViewExists($mail);
                    $this->assertSame($this->examples->staffPasswordResets[0]->email, $mail->to[0]['address']);
                    $this->assertEquals(
                        [
                            'url' => "http://localhost/password-resets/{$this->examples->staffPasswordResets[0]->token}",
                            'expiredAt' => $this->examples->staffPasswordResets[0]->expiredAt->format('n/j H:i'),
                            'loginUrl' => "https://{$this->context->organization->code}.careid.jp/",
                            'name' => $this->examples->staffs[0]->name,
                        ],
                        $mail->viewData
                    );
                    $this->assertSame("http://localhost/password-resets/{$this->examples->staffPasswordResets[0]->token}", $mail->viewData['url']);
                    $this->assertSame($this->examples->staffPasswordResets[0]->expiredAt->format('n/j H:i'), $mail->viewData['expiredAt']);
                    $this->assertSame("https://{$this->context->organization->code}.careid.jp/", $mail->viewData['loginUrl']);
                    $this->assertModelStrictEquals($this->examples->staffs[0]->name, $mail->viewData['name']);
                });

            $this->listener->handle(new CreateStaffPasswordResetEvent($this->context, $this->examples->staffPasswordResets[0]));
        });
    }
}
