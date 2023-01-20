<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Shift\LookupShiftInteractor;

/**
 * LookupShiftInteractor のテスト.
 */
class LookupShiftInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use ShiftRepositoryMixin;
    use UnitSupport;

    private LookupShiftInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupShiftInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0]);
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->interactor = app(LookupShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of shift', function (): void {
            $this->shiftRepository
                ->expects('lookup')
                ->with($this->examples->shifts[0]->id)
                ->andReturn(Seq::from($this->examples->shifts[0]));

            $actual = $this->interactor->handle($this->context, Permission::viewShifts(), $this->examples->shifts[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->shifts[0], $actual->head());
        });

        $this->should('return empty seq when accessibleTo of Context return false', function (): void {
            $this->shiftRepository
                ->expects('lookup')
                ->with($this->examples->shifts[0]->id)
                ->andReturn(Seq::from($this->examples->shifts[0]));
            $this->context
                ->expects('isAccessibleTo')
                ->andReturn(false);

            $actual = $this->interactor->handle($this->context, Permission::viewShifts(), $this->examples->shifts[0]->id);
            $this->assertCount(0, $actual);
        });
    }
}
