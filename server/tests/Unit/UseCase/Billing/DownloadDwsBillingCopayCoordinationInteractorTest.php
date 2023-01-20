<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\DownloadDwsBillingCopayCoordinationInteractor;

/**
 * {@link \UseCase\Billing\DownloadDwsBillingCopayCoordinationInteractor} のテスト.
 */
final class DownloadDwsBillingCopayCoordinationInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateFileNameUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingCopayCoordinationUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.pdf';

    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private DwsBillingCopayCoordination $copayCoordination;

    private DownloadDwsBillingCopayCoordinationInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundle = $self->examples->dwsBillingBundles[0];
            $self->copayCoordination = $self->examples->dwsBillingCopayCoordinations[0];

            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->lookupDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingCopayCoordinations[0]))
                ->byDefault();
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(DownloadDwsBillingCopayCoordinationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::downloadBillings(), $this->billing->id, $this->bundle->id)
                ->andReturn(Seq::from($this->examples->dwsBillingBundles[0]));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->copayCoordination->id
            );
        });
        $this->should('use LookupDwsBillingCopayCoordinationUseCase', function (): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadBillings(),
                    $this->billing->id,
                    $this->bundle->id,
                    $this->copayCoordination->id
                )
                ->andReturn(Seq::from($this->examples->dwsBillingCopayCoordinations[0]));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->copayCoordination->id
            );
        });
        $this->should('return DwsBillingCopayCoordination PDF params', function (): void {
            $this->assertEquals(
                [
                    'filename' => self::FILENAME,
                    'params' => [
                        'bundles' => [
                            [
                                'copayCoordinations' => [
                                    DwsBillingCopayCoordinationPdf::from($this->bundle, $this->copayCoordination),
                                ],
                            ],
                        ],
                    ],
                ],
                $this->interactor->handle(
                    $this->context,
                    $this->billing->id,
                    $this->bundle->id,
                    $this->copayCoordination->id
                )
            );
        });
    }
}
