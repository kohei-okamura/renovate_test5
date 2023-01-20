<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\StaffLoggedOutEventListener;
use Domain\Staff\StaffLoggedOutEvent;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CookieMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RemoveStaffRememberTokenUseCaseMixin;
use Tests\Unit\Test;

/**
 * StaffLoggedOutEventListener のテスト
 */
class StaffLoggedOutEventListenerTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use CookieMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RemoveStaffRememberTokenUseCaseMixin;
    use UnitSupport;

    private StaffLoggedOutEventListener $listener;

    /**
     * セットアップ処理
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffLoggedOutEventListenerTest $self): void {
            $self->removeStaffRememberTokenUseCase
                ->allows('handle')
                ->byDefault();
            $self->config
                ->allows('get')
                ->andReturn('remember_token')
                ->byDefault();
            $self->cookie
                ->allows('forget')
                ->byDefault();

            $self->listener = app(StaffLoggedOutEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use useCase to remove token.', function (): void {
            $this->removeStaffRememberTokenUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->staffRememberTokens[0]->id)
                ->andReturnNull();
            $this->listener->handle(new StaffLoggedOutEvent($this->context, Option::from($this->examples->staffRememberTokens[0]->id)));
        });
        $this->should('forget cookie by config value', function (): void {
            $cookie = 'COOKIE';
            $this->config
                ->expects('get')
                ->with('zinger.remember_token.cookie_name')
                ->andReturn($cookie);
            $this->cookie
                ->expects('forget')
                ->with($cookie)
                ->andReturn($this->cookie);
            $this->listener->handle(new StaffLoggedOutEvent($this->context, Option::from($this->examples->staffRememberTokens[0]->id)));
        });
        $this->should('succeed normally when remember token does not exist', function (): void {
            $this->listener->handle(new StaffLoggedOutEvent($this->context, Option::none()));
        });
    }
}
