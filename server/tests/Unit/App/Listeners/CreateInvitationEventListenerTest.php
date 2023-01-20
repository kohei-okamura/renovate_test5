<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\CreateInvitationEventListener;
use Domain\Staff\CreateInvitationEvent;
use Illuminate\Mail\Mailable;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MailerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Listeners\CreateInvitationEventListener} のテスト
 */
class CreateInvitationEventListenerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MailerMixin;
    use MockeryMixin;
    use UnitSupport;

    private CreateInvitationEventListener $listener;

    /**
     * セットアップ処理
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateInvitationEventListenerTest $self): void {
            $self->listener = app(CreateInvitationEventListener::class);
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
                    $this->assertSame('careid アカウントへ招待されました', $mail->subject);
                    $this->assertMailViewExists($mail);
                    $this->assertSame($this->examples->invitations[0]->email, $mail->to[0]['address']);
                    $this->assertEquals(
                        [
                            'staffName' => $this->examples->staffs[0]->name->displayName,
                            'url' => "http://localhost/invitations/{$this->examples->invitations[0]->token}",
                            'expiredAt' => $this->examples->invitations[0]->expiredAt->format('n/j H:i'),
                            'loginUrl' => "https://{$this->context->organization->code}.careid.jp/",
                        ],
                        $mail->viewData
                    );
                });

            $this->listener->handle(
                new CreateInvitationEvent(
                    $this->context,
                    $this->examples->invitations[0],
                    Option::from($this->examples->staffs[0])
                )
            );
        });
    }
}
