<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Staff\StaffLoggedOutEvent;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Staff\StaffLoggedOutInteractor;

/**
 * StaffLoggedOutInteractor のテスト.
 */
final class StaffLoggedOutInteractorTest extends Test
{
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private StaffLoggedOutInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffLoggedOutInteractorTest $self): void {
            $self->interactor = app(StaffLoggedOutInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EventDispatcher', function (): void {
            $rememberTokenId = Option::from('rememberTokenId');
            $event = new StaffLoggedOutEvent($this->context, $rememberTokenId);
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(equalTo($event))
                ->andReturnNull();
            $this->interactor->handle($this->context, $rememberTokenId);
        });
    }
}
