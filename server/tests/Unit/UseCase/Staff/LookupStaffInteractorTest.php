<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Staff\LookupStaffInteractor;

/**
 * LookupStaffInteractor のテスト.
 */
final class LookupStaffInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffRepositoryMixin;
    use UnitSupport;

    private LookupStaffInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupStaffInteractorTest $self): void {
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->staffRepository->allows('lookup')->andReturn(Seq::from($self->examples->staffs[0]))->byDefault();

            $self->interactor = app(LookupStaffInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of Staff', function (): void {
            $this->staffRepository
                ->expects('lookup')
                ->with($this->examples->staffs[0]->id)
                ->andReturn(Seq::from($this->examples->staffs[0]));
            $actual = $this->interactor->handle($this->context, Permission::viewStaffs(), $this->examples->staffs[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->staffs[0], $actual->head());
        });
        $this->should('return empty seq when Entity is not accessible', function (): void {
            $permission = Permission::viewStaffs();
            $staff = $this->examples->staffs[0];
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($staff));
            $this->context
                ->expects('isAccessibleTo')
                ->with($permission, $staff->organizationId, $staff->officeIds, $staff->id)
                ->andReturn(false);

            $actual = $this->interactor->handle($this->context, Permission::viewStaffs(), $staff->id);

            $this->assertCount(0, $actual);
        });
    }
}
