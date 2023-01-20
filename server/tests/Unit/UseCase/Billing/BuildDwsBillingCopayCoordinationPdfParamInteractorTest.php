<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamInteractor} のテスト.
 */
final class BuildDwsBillingCopayCoordinationPdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use DwsBillingCopayCoordinationFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private BuildDwsBillingCopayCoordinationPdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BuildDwsBillingCopayCoordinationPdfParamInteractorTest $self): void {
            $self->dwsBillingCopayCoordinationFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsBillingCopayCoordinations, Pagination::create()))
                ->byDefault();

            $self->interactor = app(BuildDwsBillingCopayCoordinationPdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use DwsBillingCopayCoordinationFinder', function (): void {
            $billing = $this->examples->dwsBillings[0];
            $bundle = $this->examples->dwsBillingBundles[0];
            $this->dwsBillingCopayCoordinationFinder
                ->expects('find')
                ->with(['dwsBillingId' => $billing->id, 'dwsBillingBundleId' => $bundle->id], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from($this->examples->dwsBillingCopayCoordinations, Pagination::create()));

            $this->interactor
                ->handle($this->context, $billing, Seq::from($bundle))['bundles']
                ->toArray();
        });
        $this->should('return an array of params for dws copay coordination pdf', function (): void {
            $billing = $this->examples->dwsBillings[0];
            $bundles = $this->examples->dwsBillingBundles;
            $expected = [
                'bundles' => Seq::fromArray($bundles)->map(fn (DwsBillingBundle $bundle): array => [
                    'copayCoordinations' => Seq::fromArray($this->examples->dwsBillingCopayCoordinations)
                        ->map(fn (DwsBillingCopayCoordination $copayCoordination): DwsBillingCopayCoordinationPdf => DwsBillingCopayCoordinationPdf::from(
                            $bundle,
                            $copayCoordination
                        )),
                ]),
            ];

            $actual = $this->interactor->handle($this->context, $billing, Seq::fromArray($bundles));
            $this->assertEquals(
                $expected,
                $actual
            );
            $this->assertEach(
                function (array $expectedValue, array $actualValue): void {
                    $this->assertArrayStrictEquals(
                        $expectedValue['copayCoordinations']->toArray(),
                        $actualValue['copayCoordinations']->toArray()
                    );
                },
                $expected['bundles']->toArray(),
                $actual['bundles']->toArray()
            );
        });
    }
}
