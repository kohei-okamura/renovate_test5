<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Permission\Permission;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LtcsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupLtcsBillingBundleInteractor;

/**
 * {@link \UseCase\Billing\LookupLtcsBillingBundleInteractor} のテスト.
 */
final class LookupLtcsBillingBundleInteractorTest extends Test
{
    use ContextMixin;
    use EnsureLtcsBillingUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LtcsBillingBundleRepositoryMixin;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private LtcsBillingBundle $bundle;

    private LookupLtcsBillingBundleInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];
            $self->bundle = $self->examples->ltcsBillingBundles[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsBillingBundleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->bundle))
                ->byDefault();

            $self->ensureLtcsBillingUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(LookupLtcsBillingBundleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('accept an integer as the 3rd argument', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing->id,
                $this->bundle->id
            );
            $this->assertInstanceOf(Seq::class, $actual);
        });
        $this->should('accept an instance of LtcsBilling as the 3rd argument', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing,
                $this->bundle->id
            );
            $this->assertInstanceOf(Seq::class, $actual);
        });
        $this->should('throw an InvalidArgumentException when a string given as the 3rd argument', function (): void {
            $this->assertThrows(InvalidArgumentException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    (string)$this->billing->id,
                    $this->bundle->id
                );
            });
        });
        $this->should('return a Seq of LtcsBillingBundle', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing->id,
                $this->bundle->id
            );
            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->bundle, $actual->head());
        });
        $this->should('lookup the entity from LtcsBillingBundleRepository', function (): void {
            $this->ltcsBillingBundleRepository
                ->expects('lookup')
                ->with($this->bundle->id)
                ->andReturn(Seq::from($this->bundle));

            $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing->id,
                $this->bundle->id
            );
        });
        $this->should(
            'ensure the billing is available using EnsureLtcsBillingUseCase when the 3rd argument is an integer',
            function (): void {
                $this->ensureLtcsBillingUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::viewBillings(), $this->bundle->billingId)
                    ->andReturnNull();

                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id,
                    $this->bundle->id
                );
            }
        );
        $this->should(
            'omit ensuring the billing is available when the 3rd argument is an instance of LtcsBilling',
            function (): void {
                $this->ensureLtcsBillingUseCase->expects('handle')->never();

                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing,
                    $this->bundle->id
                );
            }
        );
    }
}
