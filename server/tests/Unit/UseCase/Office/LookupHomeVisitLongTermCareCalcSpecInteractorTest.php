<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\HomeVisitLongTermCareCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupHomeVisitLongTermCareCalcSpecInteractor;

/**
 * LookupHomeVisitLongTermCareCalcSpecInteractor のテスト.
 */
class LookupHomeVisitLongTermCareCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureOfficeUseCaseMixin;
    use ExamplesConsumer;
    use HomeVisitLongTermCareCalcSpecRepositoryMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private LookupHomeVisitLongTermCareCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupHomeVisitLongTermCareCalcSpecInteractorTest $self): void {
            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->homeVisitLongTermCareCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->homeVisitLongTermCareCalcSpecs[0]))
                ->byDefault();

            $self->interactor = app(LookupHomeVisitLongTermCareCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of HomeHelpServiceCalcSpec', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                [Permission::viewInternalOffices()],
                $this->examples->offices[0]->id,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->id
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->examples->homeVisitLongTermCareCalcSpecs[0], $actual->head());
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId)
                ->andReturnNull();

            $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId, $this->examples->homeVisitLongTermCareCalcSpecs[0]->id);
        });

        $this->should('use homeVisitLongTermCareCalcSpecRepository', function (): void {
            $this->homeVisitLongTermCareCalcSpecRepository
                ->expects('lookup')
                ->with($this->examples->homeVisitLongTermCareCalcSpecs[0]->id)
                ->andReturn(Seq::from($this->examples->homeVisitLongTermCareCalcSpecs[0]));

            $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId, $this->examples->homeVisitLongTermCareCalcSpecs[0]->id);
        });
    }
}
