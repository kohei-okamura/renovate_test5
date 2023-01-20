<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\VisitingCareForPwsdCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupVisitingCareForPwsdCalcSpecInteractor;

/**
 * LookVisitingCareForPwsdCalcSpecInteractor のテスト.
 */
final class LookupVisitingCareForPwsdCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use EnsureOfficeUseCaseMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;
    use VisitingCareForPwsdCalcSpecRepositoryMixin;

    private LookupVisitingCareForPwsdCalcSpecInteractor $interactor;
    private VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupVisitingCareForPwsdCalcSpecInteractorTest $self): void {
            $self->visitingCareForPwsdCalcSpec = $self->examples->visitingCareForPwsdCalcSpecs[0];

            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->visitingCareForPwsdCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->visitingCareForPwsdCalcSpec))
                ->byDefault();

            $self->interactor = app(LookupVisitingCareForPwsdCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of VisitingCareForPwsdCalcSpec', function (): void {
            $this->visitingCareForPwsdCalcSpecRepository
                ->expects('lookup')
                ->with($this->visitingCareForPwsdCalcSpec->id)
                ->andReturn(Seq::from($this->visitingCareForPwsdCalcSpec));

            $actual = $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId, $this->visitingCareForPwsdCalcSpec->id);
            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->visitingCareForPwsdCalcSpec, $actual->head());
        });
        $this->should('return empty Seq when different officeId given', function (): void {
            $actual = $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->examples->offices[1]->id, $this->visitingCareForPwsdCalcSpec->id);
            $this->assertCount(0, $actual);
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId)
                ->andReturnNull();

            $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId, $this->visitingCareForPwsdCalcSpec->id);
        });
    }
}
