<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Role;

use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Role\LookupRoleInteractor;

/**
 * LookupRoleInteractor のテスト.
 */
final class LookupRoleInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RoleRepositoryMixin;
    use UnitSupport;

    private LookupRoleInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupRoleInteractorTest $self): void {
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();

            $self->interactor = app(LookupRoleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of Role', function (): void {
            $this->roleRepository
                ->expects('lookup')
                ->with($this->examples->roles[0]->id)
                ->andReturn(Seq::from($this->examples->roles[0]));
            $actual = $this->interactor->handle($this->context, $this->examples->roles[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->roles[0], $actual->head());
        });
        $this->should('return empty seq when different organizationId given', function (): void {
            $role = $this->examples->roles[0]->copy(['organizationId' => self::NOT_EXISTING_ID]);
            $this->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($role));

            $actual = $this->interactor->handle($this->context, $this->examples->roles[0]->id);
            $this->assertCount(0, $actual);
        });
    }
}
