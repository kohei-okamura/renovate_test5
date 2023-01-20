<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetDwsBillingServiceReportInfoInteractor;

/**
 * {@link \UseCase\Billing\GetDwsBillingServiceReportInfoInteractor} Test.
 */
class GetDwsBillingServiceReportInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingServiceReportUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingServiceReport $serviceReport;

    private GetDwsBillingServiceReportInfoInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetDwsBillingServiceReportInfoInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->serviceReport = $self->examples->dwsBillingServiceReports[2];

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingBundle))
                ->byDefault();
            $self->lookupDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->serviceReport))
                ->byDefault();

            $self->interactor = app(GetDwsBillingServiceReportInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return assoc with parameters', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->serviceReport->id
            );

            $this->assertArrayHasKey('billing', $actual);
            $this->assertArrayHasKey('bundle', $actual);
            $this->assertArrayHasKey('report', $actual);

            $this->assertModelStrictEquals($this->billing, $actual['billing']);
            $this->assertModelStrictEquals($this->billingBundle, $actual['bundle']);
            $this->assertModelStrictEquals($this->serviceReport, $actual['report']);
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->billing->id)
                ->andReturn(Seq::from($this->billing));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->serviceReport->id
            );
        });
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->billing->id, $this->billingBundle->id)
                ->andReturn(Seq::from($this->billingBundle));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->serviceReport->id
            );
        });
        $this->should('use LookupDwsBillingStatementUseCase', function (): void {
            $this->lookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->serviceReport->id
                )
                ->andReturn(Seq::from($this->serviceReport));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->serviceReport->id
            );
        });
        $this->should('throw NotFoundException when LookupDwsBillingUseCase return Empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->serviceReport->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupDwsBillingBundleUseCase return Empty', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->serviceReport->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupDwsBillingStatementUseCase return Empty', function (): void {
            $this->lookupDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->serviceReport->id
                    );
                }
            );
        });
    }
}
