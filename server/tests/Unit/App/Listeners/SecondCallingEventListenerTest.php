<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\SecondCallingEventListener;
use Domain\Calling\SecondCallingEvent;
use Domain\Calling\StaffAttendanceSmsMessage;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SmsGatewayMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Listeners\SecondCallingEventListener} Test.
 */
class SecondCallingEventListenerTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use SmsGatewayMixin;
    use UnitSupport;

    private const SHORT_URL = 'http://short_url.test/hogehoge';
    private const ANNOUNCE_MINUTES = 70;

    private SecondCallingEventListener $listener;
    private SecondCallingEvent $event;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SecondCallingEventListenerTest $self): void {
            $self->event = new SecondCallingEvent(
                $self->context,
                self::ANNOUNCE_MINUTES,
                $self->examples->callings[0],
                $self->examples->shifts[0],
                $self->examples->staffs[0],
                self::SHORT_URL,
            );
            $self->listener = app(SecondCallingEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use domain with SmsMessage', function (): void {
            $message = StaffAttendanceSmsMessage::create([
                'url' => $this->event->url(),
                'shift' => $this->event->shift(),
                'minutes' => $this->event->minutes(),
            ]);
            $this->smsGateway
                ->expects('send')
                ->with(equalTo($message), $this->event->staff()->tel);
            $this->listener->handle($this->event);
        });
    }
}
