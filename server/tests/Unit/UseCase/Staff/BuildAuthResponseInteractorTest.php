<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AggregatePermissionCodeListUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Staff\BuildAuthResponseInteractor;

/**
 * {@link \UseCase\Staff\BuildAuthResponseInteractor} Test.
 */
class BuildAuthResponseInteractorTest extends Test
{
    use AggregatePermissionCodeListUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupRoleUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private BuildAuthResponseInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BuildAuthResponseInteractorTest $self): void {
            $self->aggregatePermissionCodeListUseCase
                ->allows('handle')
                ->andReturn(Permission::all())
                ->byDefault();
            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();

            $self->interactor = app(BuildAuthResponseInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array', function (): void {
            $this->assertEquals(
                ['auth' => [
                    'isSystemAdmin' => $this->examples->roles[0]->isSystemAdmin,
                    'permissions' => Permission::all(),
                    'staff' => $this->examples->staffs[0],
                ]],
                $this->interactor->handle($this->context, $this->examples->staffs[0])
            );
        });
        $this->should('use Aggregate UseCase', function (): void {
            $this->aggregatePermissionCodeListUseCase
                ->expects('handle')
                ->with($this->context, equalTo(Seq::from($this->examples->roles[0])));

            $this->interactor->handle($this->context, $this->examples->staffs[0]);
        });
        $this->should('use Lookup UseCase', function (): void {
            $this->lookupRoleUseCase
                ->expects('handle')
                ->with($this->context, ...$this->examples->staffs[0]->roleIds)
                ->andReturn(Seq::from($this->examples->roles[1]));

            $this->interactor->handle($this->context, $this->examples->staffs[0]);
        });
    }
}
