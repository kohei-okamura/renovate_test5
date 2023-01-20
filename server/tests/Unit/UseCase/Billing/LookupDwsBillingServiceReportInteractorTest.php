<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupDwsBillingServiceReportInteractor;

/**
 * {@link \UseCase\Billing\LookupDwsBillingServiceReportInteractor} Test.
 */
class LookupDwsBillingServiceReportInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private LookupDwsBillingServiceReportInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsBillingServiceReportInteractorTest $self): void {
            $self->dwsBillingServiceReportRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillingServiceReports[0]))
                ->byDefault();
            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(LookupDwsBillingServiceReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBillingServiceReportRepository', function (): void {
            $this->dwsBillingServiceReportRepository
                ->expects('lookup')
                ->with($this->examples->dwsBillingServiceReports[0]->id)
                ->andReturn(Seq::from($this->examples->dwsBillingServiceReports[0]));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                $this->examples->dwsBillingServiceReports[0]->id,
            );
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->dwsBillingServiceReports[0], $actual->head());
        });
        $this->should('use EnsureDwsBillingBundleUseCase', function (): void {
            $this->ensureDwsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->examples->dwsBillings[0]->id,
                    $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId
                )
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                $this->examples->dwsBillingServiceReports[0]->id,
            );
        });
    }
}
