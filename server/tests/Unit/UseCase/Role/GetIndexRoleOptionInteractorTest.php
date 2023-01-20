<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Role;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Role\Role;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Role\GetIndexRoleOptionInteractor;

/**
 * {@link \UseCase\Role\GetIndexRoleOptionInteractor} のテスト.
 */
class GetIndexRoleOptionInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindRoleUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetIndexRoleOptionInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexRoleOptionInteractorTest $self): void {
            $self->findRoleUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->roles, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetIndexRoleOptionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array of user option', function (): void {
            $expected = Seq::fromArray($this->examples->roles)
                ->map(fn (Role $role): array => [
                    'text' => $role->name,
                    'value' => $role->id,
                ]);
            $actual = $this->interactor->handle($this->context, Permission::listRoles());

            $this->assertSame(
                $expected->toArray(),
                $actual->toArray()
            );
        });
        $this->should('use FindRoleUseCase', function (): void {
            $this->findRoleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->roles, Pagination::create()));

            $this->interactor->handle($this->context, Permission::listRoles());
        });
    }
}
