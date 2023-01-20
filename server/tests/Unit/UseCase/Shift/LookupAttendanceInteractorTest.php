<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Domain\Permission\Permission;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Shift\LookupAttendanceInteractor;

/**
 * {@link \UseCase\Shift\LookupAttendanceInteractor} のテスト.
 */
final class LookupAttendanceInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use AttendanceRepositoryMixin;
    use UnitSupport;

    private LookupAttendanceInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LookupAttendanceInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0]);
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->interactor = app(LookupAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of attendance', function (): void {
            $this->attendanceRepository
                ->expects('lookup')
                ->with($this->examples->attendances[0]->id)
                ->andReturn(Seq::from($this->examples->attendances[0]));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewAttendances(),
                $this->examples->attendances[0]->id
            );
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->attendances[0], $actual->head());
        });

        $this->should('return empty seq when accessibleTo of Context return false', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->with(Permission::viewAttendances(), self::NOT_EXISTING_ID, Mockery::any())
                ->andReturn(false);
            $attendance = $this->examples->attendances[0]->copy(['organizationId' => self::NOT_EXISTING_ID]);
            $this->attendanceRepository
                ->allows('lookup')
                ->andReturn(Seq::from($attendance));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewAttendances(),
                $this->examples->attendances[0]->id
            );
            $this->assertCount(0, $actual);
        });
    }
}
