<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListInteractor} のテスト.
 */
final class BuildDwsBillingCopayCoordinationRecordListInteractorTest extends Test
{
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingCopayCoordinationFinderMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private BuildDwsBillingCopayCoordinationRecordListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(BuildDwsBillingCopayCoordinationRecordListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find DwsBillingCopayCoordinations by bundle id', function (): void {
            $this->dwsBillingCopayCoordinationFinder
                ->expects(('find'))
                ->with(['dwsBillingId' => $this->bundle->id], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from($this->createCopayCoordinationSeq(), Pagination::create()));

            $this->interactor->handle($this->context, $this->billing, Seq::from($this->bundle));
        });

        $this->should('return an array of ExchangeRecords', function (): void {
            $this->dwsBillingCopayCoordinationFinder
                ->allows('find')
                ->andReturn(FinderResult::from($this->createCopayCoordinationSeq(), Pagination::create()));

            $xs = $this->interactor->handle($this->context, $this->billing, Seq::from($this->bundle));

            $this->assertMatchesModelSnapshot($xs);
        });
    }

    /**
     * テスト用の利用者負担上限額管理結果票の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Seq
     */
    private function createCopayCoordinationSeq(): Seq
    {
        return Seq::from(
            $this->createCopayCoordination(),
            $this->createCopayCoordination([
                'items' => [
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 1,
                        'office' => DwsBillingOffice::from($this->office),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 1425643,
                            'copay' => 37200,
                            'coordinatedCopay' => 37200,
                        ]),
                    ]),
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 2,
                        'office' => DwsBillingOffice::from($this->office(2, '1312700691')),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 19598,
                            'copay' => 1959,
                            'coordinatedCopay' => 0,
                        ]),
                    ]),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 1445241,
                    'copay' => 39159,
                    'coordinatedCopay' => 37200,
                ]),
            ]),
            $this->createCopayCoordination([
                'items' => [
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 1,
                        'office' => DwsBillingOffice::from($this->office),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 1273501,
                            'copay' => 9300,
                            'coordinatedCopay' => 9300,
                        ]),
                    ]),
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 2,
                        'office' => DwsBillingOffice::from($this->office(2, '1372700235')),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 73466,
                            'copay' => 7346,
                            'coordinatedCopay' => 0,
                        ]),
                    ]),
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 3,
                        'office' => DwsBillingOffice::from($this->office(3, '1313600205')),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 12960,
                            'copay' => 1296,
                            'coordinatedCopay' => 0,
                        ]),
                    ]),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 1359927,
                    'copay' => 17942,
                    'coordinatedCopay' => 9300,
                ]),
            ]),
        );
    }

    /**
     * テスト用の利用者負担上限額管理結果票を生成する.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function createCopayCoordination(array $overwrites = []): DwsBillingCopayCoordination
    {
        $x = DwsBillingCopayCoordination::create([
            'id' => 1,
            'dwsBillingId' => $this->billing->id,
            'dwsBillingBundleId' => $this->bundle->id,
            'office' => DwsBillingOffice::from($this->office),
            'user' => DwsBillingUser::from($this->user, $this->dwsCertification),
            'result' => CopayCoordinationResult::appropriated(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
            'items' => [
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 1,
                    'office' => DwsBillingOffice::from($this->office),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 494009,
                        'copay' => 9300,
                        'coordinatedCopay' => 9300,
                    ]),
                ]),
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 2,
                    'office' => DwsBillingOffice::from($this->office(2, '1313201244')),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 156059,
                        'copay' => 9300,
                        'coordinatedCopay' => 0,
                    ]),
                ]),
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 3,
                    'office' => DwsBillingOffice::from($this->office(3, '1313202283')),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 255609,
                        'copay' => 9300,
                        'coordinatedCopay' => 0,
                    ]),
                ]),
            ],
            'total' => DwsBillingCopayCoordinationPayment::create([
                'fee' => 905677,
                'copay' => 27900,
                'coordinatedCopay' => 9300,
            ]),
            'status' => DwsBillingStatus::fixed(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
        return $x->copy($overwrites);
    }
}
