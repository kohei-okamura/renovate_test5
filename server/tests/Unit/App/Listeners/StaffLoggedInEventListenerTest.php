<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Listeners;

use App\Listeners\StaffLoggedInEventListener;
use Domain\Common\Carbon;
use Domain\Staff\StaffLoggedInEvent;
use Lib\Json;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CookieMixin;
use Tests\Unit\Mixins\CreateStaffRememberTokenUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * StaffLoggedInEventListener のテスト.
 */
class StaffLoggedInEventListenerTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use CookieMixin;
    use CreateStaffRememberTokenUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    public const TOKEN_LIFETIME_DAYS = 30;
    public const TOKEN_COOKIE_NAME = 'abcdefghijk';
    private StaffLoggedInEventListener $listener;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffLoggedInEventListenerTest $self): void {
            $self->config
                ->allows('get')
                ->with('zinger.remember_token.lifetime_days')
                ->andReturn(self::TOKEN_LIFETIME_DAYS)
                ->byDefault();
            $self->config
                ->allows('get')
                ->with('zinger.remember_token.cookie_name')
                ->andReturn(self::TOKEN_COOKIE_NAME)
                ->byDefault();
            $self->cookie
                ->allows('queue')
                ->byDefault();
            $self->createRememberTokenUseCase
                ->allows('handle')
                ->byDefault();
            $self->listener = app(StaffLoggedInEventListener::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('register a cookie if a remember token is present', function (): void {
            $this->createRememberTokenUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->staffs[0])
                ->andReturn($this->examples->staffRememberTokens[0]);
            $this->config
                ->expects('get')
                ->with('zinger.remember_token.lifetime_days')
                ->andReturn(self::TOKEN_LIFETIME_DAYS);
            $this->config
                ->expects('get')
                ->with('zinger.remember_token.cookie_name')
                ->andReturn(self::TOKEN_COOKIE_NAME);
            $lifetime = Carbon::now()->addDays(self::TOKEN_LIFETIME_DAYS)->diffInMinutes();
            $this->cookie
                ->expects('queue')
                ->with(self::TOKEN_COOKIE_NAME, $this->rememberTokenJson(), $lifetime)
                ->andReturnNull();

            $this->listener->handle(new StaffLoggedInEvent($this->context, $this->examples->staffs[0], true));
        });

        $this->should('no cookie is registered without remember token', function (): void {
            $this->cookie->shouldNotReceive('queue');

            $this->listener->handle(new StaffLoggedInEvent($this->context, $this->examples->staffs[0], false));
        });
    }

    /**
     * リメンバートークンをJsonに変換.
     *
     * @return string
     */
    private function rememberTokenJson(): string
    {
        return Json::encode([
            'id' => $this->examples->staffRememberTokens[0]->id,
            'staffId' => $this->examples->staffRememberTokens[0]->staffId,
            'token' => $this->examples->staffRememberTokens[0]->token,
        ]);
    }
}
