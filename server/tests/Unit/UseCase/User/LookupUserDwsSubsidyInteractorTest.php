<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Permission\Permission;
use Domain\User\UserDwsSubsidy;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserDwsSubsidyRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\LookupUserDwsSubsidyInteractor;

/**
 * LookupUserDwsSubsidyInteractor のテスト.
 */
final class LookupUserDwsSubsidyInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UserDwsSubsidyRepositoryMixin;
    use UnitSupport;

    private LookupUserDwsSubsidyInteractor $interactor;
    private UserDwsSubsidy $userDwsSubsidy;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupUserDwsSubsidyInteractorTest $self): void {
            $self->userDwsSubsidy = $self->examples->userDwsSubsidies[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userDwsSubsidyRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->userDwsSubsidy))
                ->byDefault();

            $self->interactor = app(LookupUserDwsSubsidyInteractor::class);
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
                ->with($this->context, Permission::viewUserDwsSubsidies(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::viewUserDwsSubsidies(), $this->examples->users[0]->id, $this->userDwsSubsidy->id);
        });
        $this->should('return a Seq of Subsidy', function (): void {
            $this->userDwsSubsidyRepository
                ->expects('lookup')
                ->with($this->userDwsSubsidy->id)
                ->andReturn(Seq::from($this->userDwsSubsidy));

            $actual = $this->interactor->handle($this->context, Permission::viewUserDwsSubsidies(), $this->userDwsSubsidy->userId, $this->userDwsSubsidy->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->userDwsSubsidy, $actual->head());
        });
        $this->should('return empty Seq when different userId given', function (): void {
            $this->userDwsSubsidyRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->userDwsSubsidy));

            $actual = $this->interactor->handle($this->context, Permission::viewUserDwsSubsidies(), $this->examples->userDwsSubsidies[2]->userId, $this->userDwsSubsidy->id);
            $this->assertCount(0, $actual);
        });
    }
}
