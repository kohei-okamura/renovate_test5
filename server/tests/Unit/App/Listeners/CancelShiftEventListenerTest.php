<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\CancelShiftEventListener;
use Domain\Shift\CancelShiftEvent;
use Illuminate\Mail\Mailable;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MailerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Listeners\CancelShiftEventListener} Test.
 */
class CancelShiftEventListenerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MailerMixin;
    use MockeryMixin;
    use UnitSupport;

    private CancelShiftEventListener $listener;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelShiftEventListenerTest $self): void {
            $self->listener = app(CancelShiftEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('mail to build mail by Builder', function (): void {
            $schedule = $this->examples->shifts[0]->schedule->date->isoFormat('M月D日（ddd） ')
                . $this->examples->shifts[0]->schedule->start->format('H:i〜')
                . $this->examples->shifts[0]->schedule->end->format('H:i');
            $this->mailer
                ->expects('send')
                ->andReturnUsing(function (Mailable $mail) use ($schedule): void {
                    $this->assertSame('勤務シフトがキャンセルされました', $mail->subject);
                    $this->assertMailViewExists($mail);
                    $this->assertSame($this->examples->staffs[0]->email, $mail->to[0]['address']);
                    $this->assertEquals(
                        [
                            'schedule' => $schedule,
                            'userName' => $this->examples->users[0]->name->displayName,
                            'note' => $this->examples->shifts[0]->note,
                            'loginUrl' => "https://{$this->context->organization->code}.careid.jp/",
                            'staff' => $this->examples->staffs[0],
                        ],
                        $mail->viewData
                    );
                    $this->assertSame($schedule, $mail->viewData['schedule']);
                    $this->assertSame($this->examples->users[0]->name->displayName, $mail->viewData['userName']);
                    $this->assertSame($this->examples->shifts[0]->note, $mail->viewData['note']);
                    $this->assertSame('https://eustylelab1.careid.jp/', $mail->viewData['loginUrl']);
                    $this->assertModelStrictEquals($this->examples->staffs[0], $mail->viewData['staff']);
                });

            $this->listener
                ->handle(new CancelShiftEvent(
                    $this->context,
                    $this->examples->shifts[0],
                    $this->examples->staffs[0],
                    $this->examples->users[0],
                ));
        });
    }
}
