<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Permission\Permission;
use Domain\Project\DwsProject;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProjectRepositoryMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Project\LookupDwsProjectInteractor;

/**
 * LookupDwsProjectInteractor のテスト.
 */
final class LookupDwsProjectInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;
    use DwsProjectRepositoryMixin;

    private LookupDwsProjectInteractor $interactor;
    private DwsProject $dwsProject;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsProjectInteractorTest $self): void {
            $self->dwsProject = $self->examples->dwsProjects[0];

            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->dwsProjectRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dwsProject))
                ->byDefault();

            $self->interactor = app(LookupDwsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of DwsProject', function (): void {
            $actual = $this->interactor->handle($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, $this->dwsProject->id);
            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->dwsProject, $actual->head());
        });
        $this->should('return empty Seq when different officeId given', function (): void {
            $actual = $this->interactor->handle($this->context, Permission::viewDwsProjects(), $this->examples->users[1]->id, $this->dwsProject->id);
            $this->assertCount(0, $actual);
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, $this->dwsProject->id);
        });
        $this->should('use dwsProjectRepository', function (): void {
            $this->dwsProjectRepository
                ->expects('lookup')
                ->with($this->dwsProject->id)
                ->andReturn(Seq::from($this->dwsProject));

            $this->interactor->handle($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, $this->dwsProject->id);
        });
    }
}
