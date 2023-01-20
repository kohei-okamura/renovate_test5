<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\EnsureDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupDwsBillingCopayCoordinationInteractor;

/**
 * {@link \UseCase\Billing\LookupDwsBillingCopayCoordinationInteractor} Test.
 */
class LookupDwsBillingCopayCoordinationInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingCopayCoordinationRepositoryMixin;
    use EnsureDwsBillingUseCaseMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private LookupDwsBillingCopayCoordinationInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsBillingCopayCoordinationInteractorTest $self): void {
            $self->dwsBillingCopayCoordinationRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillingCopayCoordinations[0]))
                ->byDefault();
            $self->ensureDwsBillingUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(LookupDwsBillingCopayCoordinationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBillingCopayCoordination', function (): void {
            $this->dwsBillingCopayCoordinationRepository
                ->expects('lookup')
                ->with($this->examples->dwsBillingCopayCoordinations[0]->id)
                ->andReturn(Seq::from($this->examples->dwsBillingCopayCoordinations[0]));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId,
                $this->examples->dwsBillingCopayCoordinations[0]->id,
            );
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->dwsBillingCopayCoordinations[0], $actual->head());
        });
        $this->should('use EnsureDwsBillingBundleUseCase', function (): void {
            $this->ensureDwsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->examples->dwsBillings[0]->id,
                    $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId
                )
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId,
                $this->examples->dwsBillingCopayCoordinations[0]->id,
            );
        });
    }
}
