<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Permission\Permission;
use Domain\User\UserLtcsSubsidy;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserLtcsSubsidyRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\LookupUserLtcsSubsidyInteractor;

/**
 * LookupUserLtcsSubsidyInteractor のテスト.
 */
final class LookupUserLtcsSubsidyInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UserLtcsSubsidyRepositoryMixin;
    use UnitSupport;

    private LookupUserLtcsSubsidyInteractor $interactor;
    private UserLtcsSubsidy $userLtcsSubsidy;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupUserLtcsSubsidyInteractorTest $self): void {
            $self->userLtcsSubsidy = $self->examples->userLtcsSubsidies[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userLtcsSubsidyRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->userLtcsSubsidy))
                ->byDefault();

            $self->interactor = app(LookupUserLtcsSubsidyInteractor::class);
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
                ->with($this->context, Permission::viewUserLtcsSubsidies(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::viewUserLtcsSubsidies(), $this->examples->users[0]->id, $this->userLtcsSubsidy->id);
        });
        $this->should('return a Seq of Subsidy', function (): void {
            $this->userLtcsSubsidyRepository
                ->expects('lookup')
                ->with($this->userLtcsSubsidy->id)
                ->andReturn(Seq::from($this->userLtcsSubsidy));

            $actual = $this->interactor->handle($this->context, Permission::viewUserLtcsSubsidies(), $this->userLtcsSubsidy->userId, $this->userLtcsSubsidy->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->userLtcsSubsidy, $actual->head());
        });
        $this->should('return empty Seq when different userId given', function (): void {
            $this->userLtcsSubsidyRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->userLtcsSubsidy));

            $actual = $this->interactor->handle($this->context, Permission::viewUserLtcsSubsidies(), $this->examples->userLtcsSubsidies[1]->userId, $this->userLtcsSubsidy->id);
            $this->assertCount(0, $actual);
        });
    }
}
