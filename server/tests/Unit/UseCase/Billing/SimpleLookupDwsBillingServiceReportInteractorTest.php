<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\SimpleLookupDwsBillingServiceReportInteractor;

/**
 * {@link \UseCase\Billing\SimpleLookupDwsBillingServiceReportInteractor} のテスト.
 */
final class SimpleLookupDwsBillingServiceReportInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingServiceReport $serviceReport;
    private SimpleLookupDwsBillingServiceReportInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->serviceReport = $self->examples->dwsBillingServiceReports[0];

            $self->dwsBillingServiceReportRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->serviceReport))
                ->byDefault();
            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(SimpleLookupDwsBillingServiceReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBillingServiceReport', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->serviceReport->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->serviceReport,
                $actual->head()
            );
        });
        $this->should('use EnsureDwsBillingUseCase', function (): void {
            $this->ensureDwsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->serviceReport->dwsBillingId,
                    $this->serviceReport->dwsBillingBundleId
                )
                ->andReturnNull();

            $this->assertCount(
                1,
                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->serviceReport->id
                )
            );
        });
        $this->should('return empty when Repository returns not match BundleId.', function (): void {
            $errorServiceReport = $this->examples->dwsBillingServiceReports[1]->copy(['dwsBillingBundleId' => self::NOT_EXISTING_ID]);
            $this->dwsBillingServiceReportRepository
                ->allows('lookup')
                ->andReturn(Seq::from(
                    $this->examples->dwsBillingServiceReports[0],
                    $errorServiceReport,
                ));
            $this->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->andThrow(new NotFoundException("DwsBillingBundle({$errorServiceReport->dwsBillingBundleId}) not found"));

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Permission::viewBillings(),
                        $this->examples->dwsBillingServiceReports[0]->id,
                        $this->examples->dwsBillingServiceReports[1]->id
                    );
                }
            );
        });
    }
}
