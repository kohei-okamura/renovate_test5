<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Exception;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingBundleListInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingBundleListInteractor} のテスト.
 */
final class CreateDwsBillingBundleListInteractorTest extends Test
{
    use BuildDwsBillingServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingBundleRepositoryMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Seq $serviceDetailList;

    private CreateDwsBillingBundleListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->serviceDetailList = $self->serviceDetailList();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->buildDwsBillingServiceDetailListUseCase
                ->allows('handle')
                ->andReturn($self->serviceDetailList)
                ->byDefault();

            $self->dwsBillingBundleRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingBundle $x): DwsBillingBundle => $x->copy(['id' => 1]))
                ->byDefault();

            $self->interactor = app(CreateDwsBillingBundleListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn(Seq::emptySeq());
            $this->buildDwsBillingServiceDetailListUseCase->expects('handle')->never();
            $this->dwsBillingBundleRepository->expects('store')->never();

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->billing,
                $this->providedIn,
                $this->sources
            );
        });
        $this->should(
            'store each of DwsBillingBundle',
            function (array $data): void {
                $this->dwsBillingBundleRepository
                    ->expects('store')
                    ->withArgs(fn (DwsBillingBundle $x): bool => $x->equals(DwsBillingBundle::create([
                        'dwsBillingId' => $this->billing->id,
                        'providedIn' => $this->providedIn,
                        'cityCode' => $data['cityCode'],
                        'cityName' => $data['cityName'],
                        'details' => $data['details'],
                        'createdAt' => Carbon::now(),
                        'updatedAt' => Carbon::now(),
                    ])))
                    ->andReturnUsing(fn (DwsBillingBundle $x): DwsBillingBundle => $x->copy(['id' => 1]));

                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->billing,
                    $this->providedIn,
                    $this->sources
                );
            },
            ['examples' => $this->serviceDetailList->map(fn (array $x): array => [$x])->toArray()]
        );
        $this->should('throw an Exception when BuildDwsBillingServiceDetailListUseCase throws it', function (): void {
            $this->buildDwsBillingServiceDetailListUseCase->expects('handle')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->billing,
                    $this->providedIn,
                    $this->sources
                );
            });
        });
        $this->should('throw an Exception when BuildDwsBillingBundleRepository throws it', function (): void {
            $this->dwsBillingBundleRepository->expects('store')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->billing,
                    $this->providedIn,
                    $this->sources
                );
            });
        });
        $this->should('return Seq of DwsBillingBundle', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->office,
                $this->billing,
                $this->providedIn,
                $this->sources
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト用の障害福祉サービス：請求：サービス詳細の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    private function serviceDetailList(): Seq
    {
        $endOfMonth = $this->providedIn->endOfMonth()->startOfDay();
        return Seq::from(
            [
                'cityCode' => '141421',
                'cityName' => '米花市',
                'details' => [
                    $this->serviceDetail($this->users[1]->id, $this->providedIn->day(3), '111147', DwsServiceCodeCategory::physicalCare(), 1139),
                    $this->serviceDetail($this->users[1]->id, $this->providedIn->day(7), '117667', DwsServiceCodeCategory::housework(), 438),
                    $this->serviceDetail($this->users[1]->id, $endOfMonth, '116010', DwsServiceCodeCategory::specifiedOfficeAddition1(), 315, true),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124171', DwsServiceCodeCategory::visitingCareForPwsd1(), 318),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124181', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124391', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124401', DwsServiceCodeCategory::visitingCareForPwsd1(), 158),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124411', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124421', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124431', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124121', DwsServiceCodeCategory::visitingCareForPwsd1(), 147, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '122121', DwsServiceCodeCategory::visitingCareForPwsd1(), 123, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '121131', DwsServiceCodeCategory::visitingCareForPwsd1(), 98, false, 8),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '121141', DwsServiceCodeCategory::visitingCareForPwsd1(), 92, false, 8),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '121151', DwsServiceCodeCategory::visitingCareForPwsd1(), 99, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '123151', DwsServiceCodeCategory::visitingCareForPwsd1(), 124, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '123161', DwsServiceCodeCategory::visitingCareForPwsd1(), 115, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124161', DwsServiceCodeCategory::visitingCareForPwsd1(), 138, false, 4),
                ],
            ],
            [
                'cityCode' => '173205',
                'cityName' => '古糸市',
                'details' => [
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124371', DwsServiceCodeCategory::visitingCareForPwsd3(), 276),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124381', DwsServiceCodeCategory::visitingCareForPwsd3(), 135),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124491', DwsServiceCodeCategory::visitingCareForPwsd3(), 138),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124501', DwsServiceCodeCategory::visitingCareForPwsd3(), 137),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124511', DwsServiceCodeCategory::visitingCareForPwsd3(), 138),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124521', DwsServiceCodeCategory::visitingCareForPwsd3(), 135),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124531', DwsServiceCodeCategory::visitingCareForPwsd3(), 138),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124321', DwsServiceCodeCategory::visitingCareForPwsd3(), 128, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '122321', DwsServiceCodeCategory::visitingCareForPwsd3(), 106, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '121331', DwsServiceCodeCategory::visitingCareForPwsd3(), 85, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '121341', DwsServiceCodeCategory::visitingCareForPwsd3(), 80, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '121351', DwsServiceCodeCategory::visitingCareForPwsd3(), 86, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '123351', DwsServiceCodeCategory::visitingCareForPwsd3(), 108, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '123361', DwsServiceCodeCategory::visitingCareForPwsd3(), 100, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124361', DwsServiceCodeCategory::visitingCareForPwsd3(), 120, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124171', DwsServiceCodeCategory::visitingCareForPwsd1(), 318),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124181', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124391', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124401', DwsServiceCodeCategory::visitingCareForPwsd1(), 158),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124411', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124421', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124431', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124121', DwsServiceCodeCategory::visitingCareForPwsd1(), 147, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '122121', DwsServiceCodeCategory::visitingCareForPwsd1(), 123, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '121131', DwsServiceCodeCategory::visitingCareForPwsd1(), 98, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '121141', DwsServiceCodeCategory::visitingCareForPwsd1(), 92, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '121151', DwsServiceCodeCategory::visitingCareForPwsd1(), 99, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '123151', DwsServiceCodeCategory::visitingCareForPwsd1(), 124, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '123161', DwsServiceCodeCategory::visitingCareForPwsd1(), 115, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124161', DwsServiceCodeCategory::visitingCareForPwsd1(), 138, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128453', DwsServiceCodeCategory::outingSupportForPwsd(), 100),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128457', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128461', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128465', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128469', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128473', DwsServiceCodeCategory::outingSupportForPwsd(), 50),
                    $this->serviceDetail($this->users[2]->id, $endOfMonth, '126010', DwsServiceCodeCategory::specifiedOfficeAddition1(), 4446, true),
                ],
            ],
        );
    }
}
