<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\ThirdCallingEventListener;
use Domain\Calling\ThirdCallingEvent;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TelGatewayMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Listeners\ThirdCallingEventListener} Test.
 */
class ThirdCallingEventListenerTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use TelGatewayMixin;
    use UnitSupport;

    private const URI = 'http://url.test/audio_file.mp3';

    private ThirdCallingEventListener $listener;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ThirdCallingEventListenerTest $self): void {
            $self->config
                ->allows('get')
                ->with('zinger.staff_attendance_confirmation.third.audio_uri')
                ->andReturn(self::URI)
                ->byDefault();

            $self->listener = app(ThirdCallingEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call gateway to call tel', function (): void {
            $staff = $this->examples->staffs[0];
            $this->telGateway
                ->expects('call')
                ->with(self::URI, $staff->tel)
                ->andReturnNull();

            $this->listener->handle(new ThirdCallingEvent($this->context, $staff));
        });
    }
}
