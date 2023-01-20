<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Staff\StaffRememberToken;
use Lib\Exceptions\RuntimeException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffRememberTokenRepositoryMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\CreateStaffRememberTokenInteractor;

/**
 * CreateStaffRememberTokenInteractor のテスト.
 */
final class CreateStaffRememberTokenInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use StaffRememberTokenRepositoryMixin;
    use TokenMakerMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const REMEMBER_TOKEN_LIFETIME_DAYS = 999;
    private const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private CreateStaffRememberTokenInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateStaffRememberTokenInteractorTest $self): void {
            $self->config
                ->allows('get')
                ->with('zinger.remember_token.lifetime_days')
                ->andReturn(self::REMEMBER_TOKEN_LIFETIME_DAYS);

            $self->staffRememberTokenRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::none())
                ->byDefault();
            $self->staffRememberTokenRepository
                ->allows('store')
                ->andReturn($self->examples->staffRememberTokens[0])
                ->byDefault();
            $self->staffRememberTokenRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->byDefault();

            $self->logger->allows('info')->byDefault();

            $self->tokenMaker->allows('make')->andReturn(self::TOKEN)->byDefault();

            $self->interactor = app(CreateStaffRememberTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a StaffRememberToken', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->staffRememberTokens[0],
                $this->interactor->handle($this->context, $this->examples->staffs[0])
            );
        });
        $this->should('store the StaffRememberToken', function (): void {
            $this->staffRememberTokenRepository
                ->expects('store')
                ->withArgs(function (StaffRememberToken $x): bool {
                    return $x->staffId === $this->examples->staffs[0]->id
                        && strlen($x->token) === 60
                        && $x->expiredAt->equalTo(Carbon::now()->addDays(self::REMEMBER_TOKEN_LIFETIME_DAYS))
                        && $x->createdAt->equalTo(Carbon::now());
                })
                ->andReturn($this->examples->staffRememberTokens[0]);

            $this->interactor->handle($this->context, $this->examples->staffs[0]);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    'スタッフリメンバートークンが登録されました',
                    [
                        'id' => $this->examples->staffRememberTokens[0]->id,
                        'staffId' => $this->examples->staffs[0]->id,
                    ] + $context
                );

            $this->interactor->handle($this->context, $this->examples->staffs[0]);
        });
        $this->should('throw a RuntimeException when failed to make unique token', function (): void {
            $this->staffRememberTokenRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($this->examples->staffRememberTokens[0]));

            $this->assertThrows(
                RuntimeException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->staffs[0]);
                }
            );
        });
        $this->should('make a token when it is required', function (): void {
            $this->tokenMaker->expects('make')->times(50)->andReturn(self::TOKEN);
            $this->staffRememberTokenRepository
                ->expects('lookupOptionByToken')
                ->times(49)
                ->andReturn(Option::from($this->examples->staffRememberTokens[0]));
            $this->staffRememberTokenRepository
                ->expects('lookupOptionByToken')
                ->andReturn(Option::none());

            $this->interactor->handle($this->context, $this->examples->staffs[0]);
        });
    }
}
