<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\UpdateShiftEventListener;
use Domain\Shift\UpdateShiftEvent;
use Illuminate\Mail\Mailable;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MailerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * UpdateShiftEventListener のテスト
 */
class UpdateShiftEventListenerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MailerMixin;
    use MockeryMixin;
    use UnitSupport;

    private UpdateShiftEventListener $listener;

    /**
     * セットアップ処理
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateShiftEventListenerTest $self): void {
            $self->listener = app(UpdateShiftEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('mail to build mail by Builder', function (): void {
            $originalSchedule = $this->examples->shifts[0]->schedule->date->isoFormat('M月D日（ddd） ')
                . $this->examples->shifts[0]->schedule->start->format('H:i〜')
                . $this->examples->shifts[0]->schedule->end->format('H:i');
            $updatedSchedule = $this->examples->shifts[1]->schedule->date->isoFormat('M月D日（ddd） ')
                . $this->examples->shifts[1]->schedule->start->format('H:i〜')
                . $this->examples->shifts[1]->schedule->end->format('H:i');
            $this->mailer
                ->expects('send')
                ->andReturnUsing(function (Mailable $mail) use ($originalSchedule, $updatedSchedule): void {
                    $this->assertSame('勤務シフトが変更されました', $mail->subject);
                    $this->assertMailViewExists($mail);
                    $this->assertSame($this->examples->staffs[0]->email, $mail->to[0]['address']);
                    $this->assertEquals(
                        [
                            'staff' => $this->examples->staffs[0],
                            'originalSchedule' => $originalSchedule,
                            'updatedSchedule' => $updatedSchedule,
                            'originalUserName' => $this->examples->users[0]->name->displayName,
                            'updatedUserName' => $this->examples->users[1]->name->displayName,
                            'note' => $this->examples->shifts[1]->note,
                            'loginUrl' => "https://{$this->context->organization->code}.careid.jp/",
                        ],
                        $mail->viewData
                    );
                    $this->assertModelStrictEquals($this->examples->staffs[0], $mail->viewData['staff']);
                    $this->assertSame($originalSchedule, $mail->viewData['originalSchedule']);
                    $this->assertSame($updatedSchedule, $mail->viewData['updatedSchedule']);
                    $this->assertSame($this->examples->users[0]->name->displayName, $mail->viewData['originalUserName']);
                    $this->assertSame($this->examples->users[1]->name->displayName, $mail->viewData['updatedUserName']);
                    $this->assertSame($this->examples->shifts[1]->note, $mail->viewData['note']);
                    $this->assertSame("https://{$this->context->organization->code}.careid.jp/", $mail->viewData['loginUrl']);
                });

            $this->listener->handle(new UpdateShiftEvent(
                $this->context,
                $this->examples->shifts[0],
                $this->examples->shifts[1],
                $this->examples->users[0],
                $this->examples->users[1],
                $this->examples->staffs[0]
            ));
        });
    }
}
