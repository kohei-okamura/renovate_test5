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
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Test;
use UseCase\Staff\AggregatePermissionCodeListInteractor;

/**
 * {@link \UseCase\Staff\AggregatePermissionCodeListInteractor} Test.
 */
class AggregatePermissionCodeListInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private AggregatePermissionCodeListInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AggregatePermissionCodeListInteractorTest $self): void {
            $self->interactor = app(AggregatePermissionCodeListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return an array of permissions', function (): void {
            $actual = $this->interactor->handle($this->context, Seq::from($this->examples->roles[0]));

            $this->assertIsArray($actual);
            $this->assertForAll($actual, fn ($x) => $x instanceof Permission);
        });
        $this->should('return merged permissions when staff is not system admin', function (): void {
            $roles = [
                $this->examples->roles[0]->copy([
                    'isSystemAdmin' => false,
                    'permissions' => [Permission::listStaffs(), Permission::viewStaffs()],
                ]),
                $this->examples->roles[1]->copy([
                    'isSystemAdmin' => false,
                    'permissions' => [Permission::viewStaffs(), Permission::createStaffs()],
                ]),
            ];

            $this->assertSame(
                [Permission::listStaffs(), Permission::viewStaffs(), Permission::createStaffs()],
                $this->interactor->handle($this->context, Seq::fromArray($roles))
            );
        });
        $this->should('return all permissions when staff is system admin', function (): void {
            $roles = [
                $this->examples->roles[0]->copy([
                    'isSystemAdmin' => false,
                    'permissions' => [Permission::listStaffs()],
                ]),
                $this->examples->roles[1]->copy([
                    'isSystemAdmin' => true,
                    'permissions' => [Permission::viewStaffs()],
                ]),
            ];

            $this->assertSame(Permission::all(), $this->interactor->handle($this->context, Seq::fromArray($roles)));
        });
    }
}
