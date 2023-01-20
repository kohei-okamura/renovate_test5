<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Permission\Permission;
use Domain\User\UserDwsCalcSpec;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserDwsCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\LookupUserDwsCalcSpecInteractor;

/**
 * LookupUserDwsCalcSpecInteractorTest のテスト.
 */
final class LookupUserDwsCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UserDwsCalcSpecRepositoryMixin;
    use UnitSupport;

    private LookupUserDwsCalcSpecInteractor $interactor;
    private UserDwsCalcSpec $userDwsCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupUserDwsCalcSpecInteractorTest $self): void {
            $self->userDwsCalcSpec = $self->examples->userDwsCalcSpecs[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userDwsCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->userDwsCalcSpec))
                ->byDefault();

            $self->interactor = app(LookupUserDwsCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserDwsCalcSpecs(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::updateUserDwsCalcSpecs(), $this->examples->users[0]->id, $this->userDwsCalcSpec->id);
        });
        $this->should('return a Seq of DwsCalcSpec', function (): void {
            $this->userDwsCalcSpecRepository
                ->expects('lookup')
                ->with($this->userDwsCalcSpec->id)
                ->andReturn(Seq::from($this->userDwsCalcSpec));

            $actual = $this->interactor->handle($this->context, Permission::updateUserDwsCalcSpecs(), $this->userDwsCalcSpec->userId, $this->userDwsCalcSpec->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->userDwsCalcSpec, $actual->head());
        });
        $this->should('return empty Seq when different userId given', function (): void {
            $this->userDwsCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->userDwsCalcSpec));

            $actual = $this->interactor->handle($this->context, Permission::updateUserDwsCalcSpecs(), $this->examples->userDwsCalcSpecs[2]->userId, $this->userDwsCalcSpec->id);
            $this->assertCount(0, $actual);
        });
    }
}
