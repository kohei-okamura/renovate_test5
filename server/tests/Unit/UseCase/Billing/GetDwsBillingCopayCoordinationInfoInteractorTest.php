<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetDwsBillingCopayCoordinationInfoInteractor;

/**
 * {@link \UseCase\Billing\GetDwsBillingCopayCoordinationInfoInteractor} Test.
 */
class GetDwsBillingCopayCoordinationInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingCopayCoordinationUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingCopayCoordination $billingCopayCoordination;

    private GetDwsBillingCopayCoordinationInfoInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetDwsBillingCopayCoordinationInfoInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->billingCopayCoordination = $self->examples->dwsBillingCopayCoordinations[2];

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingBundle))
                ->byDefault();
            $self->lookupDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingCopayCoordination))
                ->byDefault();

            $self->interactor = app(GetDwsBillingCopayCoordinationInfoInteractor::class);
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
                $this->billingCopayCoordination->id
            );

            $this->assertArrayHasKey('billing', $actual);
            $this->assertArrayHasKey('bundle', $actual);
            $this->assertArrayHasKey('copayCoordination', $actual);

            $this->assertModelStrictEquals($this->billing, $actual['billing']);
            $this->assertModelStrictEquals($this->billingBundle, $actual['bundle']);
            $this->assertModelStrictEquals($this->billingCopayCoordination, $actual['copayCoordination']);
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
                $this->billingCopayCoordination->id
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
                $this->billingCopayCoordination->id
            );
        });
        $this->should('use LookupDwsBillingCopayCoordinationUseCase', function (): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingCopayCoordination->id
                )
                ->andReturn(Seq::from($this->billingCopayCoordination));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingCopayCoordination->id
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
                        $this->billingCopayCoordination->id
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
                        $this->billingCopayCoordination->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupDwsBillingStatementUseCase return Empty', function (): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->billingCopayCoordination->id
                    );
                }
            );
        });
    }
}
