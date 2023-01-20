<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Permission\Permission;
use Domain\User\UserLtcsCalcSpec;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserLtcsCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\LookupUserLtcsCalcSpecInteractor;

/**
 * LookupUserLtcsCalcSpecInteractorTest のテスト.
 */
final class LookupUserLtcsCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UserLtcsCalcSpecRepositoryMixin;
    use UnitSupport;

    private LookupUserLtcsCalcSpecInteractor $interactor;
    private UserLtcsCalcSpec $userLtcsCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupUserLtcsCalcSpecInteractorTest $self): void {
            $self->userLtcsCalcSpec = $self->examples->userLtcsCalcSpecs[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userLtcsCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->userLtcsCalcSpec))
                ->byDefault();

            $self->interactor = app(LookupUserLtcsCalcSpecInteractor::class);
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
                ->with($this->context, Permission::updateUserLtcsCalcSpecs(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::updateUserLtcsCalcSpecs(), $this->examples->users[0]->id, $this->userLtcsCalcSpec->id);
        });
        $this->should('return a Seq of LtcsCalcSpec', function (): void {
            $this->userLtcsCalcSpecRepository
                ->expects('lookup')
                ->with($this->userLtcsCalcSpec->id)
                ->andReturn(Seq::from($this->userLtcsCalcSpec));

            $actual = $this->interactor->handle($this->context, Permission::updateUserLtcsCalcSpecs(), $this->userLtcsCalcSpec->userId, $this->userLtcsCalcSpec->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->userLtcsCalcSpec, $actual->head());
        });
        $this->should('return empty Seq when different userId given', function (): void {
            $this->userLtcsCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->userLtcsCalcSpec));

            $actual = $this->interactor->handle($this->context, Permission::updateUserLtcsCalcSpecs(), $this->examples->userLtcsCalcSpecs[2]->userId, $this->userLtcsCalcSpec->id);
            $this->assertCount(0, $actual);
        });
    }
}
