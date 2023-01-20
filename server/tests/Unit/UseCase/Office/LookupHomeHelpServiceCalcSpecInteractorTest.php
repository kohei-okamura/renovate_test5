<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\HomeHelpServiceCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupHomeHelpServiceCalcSpecInteractor;

/**
 * LookHomeHelpServiceCalcSpecInteractor のテスト.
 */
final class LookupHomeHelpServiceCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use EnsureOfficeUseCaseMixin;
    use ExamplesConsumer;
    use HomeHelpServiceCalcSpecRepositoryMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;

    private LookupHomeHelpServiceCalcSpecInteractor $interactor;
    private HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupHomeHelpServiceCalcSpecInteractorTest $self): void {
            $self->homeHelpServiceCalcSpec = $self->examples->homeHelpServiceCalcSpecs[0];

            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->homeHelpServiceCalcSpecRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->homeHelpServiceCalcSpec))
                ->byDefault();

            $self->interactor = app(LookupHomeHelpServiceCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->homeHelpServiceCalcSpec->officeId)
                ->andReturnNull();

            $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->homeHelpServiceCalcSpec->officeId, $this->homeHelpServiceCalcSpec->id);
        });
        $this->should('return a seq of HomeHelpServiceCalcSpec', function (): void {
            $this->homeHelpServiceCalcSpecRepository
                ->expects('lookup')
                ->with($this->homeHelpServiceCalcSpec->id)
                ->andReturn(Seq::from($this->homeHelpServiceCalcSpec));

            $actual = $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->homeHelpServiceCalcSpec->officeId, $this->homeHelpServiceCalcSpec->id);
            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->homeHelpServiceCalcSpec, $actual->head());
        });
        $this->should('return empty Seq when different officeId given', function (): void {
            $actual = $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->examples->offices[1]->id, $this->homeHelpServiceCalcSpec->id);
            $this->assertCount(0, $actual);
        });
    }
}
