<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsProjectRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Project\LookupLtcsProjectInteractor;

/**
 * LookupLtcsProjectInteractor のテスト.
 */
class LookupLtcsProjectInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LtcsProjectRepositoryMixin;
    use MockeryMixin;
    use UnitSupport;

    private LtcsProject $ltcsProject;
    private LookupLtcsProjectInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupLtcsProjectInteractorTest $self): void {
            $self->ltcsProject = $self->examples->ltcsProjects[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->ltcsProjectRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->ltcsProject))
                ->byDefault();

            $self->interactor = app(LookupLtcsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of LtcsProject', function (): void {
            $actual = $this->interactor->handle($this->context, Permission::viewLtcsProjects(), $this->ltcsProject->userId, $this->ltcsProject->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->ltcsProject, $actual->head());
        });
        $this->should('return an empty Seq when different userId given', function (): void {
            $actual = $this->interactor->handle($this->context, Permission::viewLtcsProjects(), $this->examples->users[1]->id, $this->ltcsProject->id);

            $this->assertCount(0, $actual);
        });
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewLtcsProjects(), $this->ltcsProject->userId)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::viewLtcsProjects(), $this->ltcsProject->userId, $this->ltcsProject->id);
        });
        $this->should('use ltcsProjectRepository', function (): void {
            $this->ltcsProjectRepository
                ->expects('lookup')
                ->with($this->ltcsProject->id)
                ->andReturn(Seq::from($this->ltcsProject));

            $this->interactor->handle($this->context, Permission::viewLtcsProjects(), $this->ltcsProject->userId, $this->ltcsProject->id);
        });
    }
}
