<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Decimal;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\UserBilling\UserBillingDwsItem;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingDwsItem} のテスト
 */
final class UserBillingDwsItemTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /** @var \Domain\Billing\DwsBillingStatementItem[] */
    private array $homeHelpServiceItems;
    private DwsBillingStatementAggregate $homeHelpServiceAggregate;

    /** @var \Domain\Billing\DwsBillingStatementItem[] */
    private array $visitingCareForPwsdItems;
    private DwsBillingStatementAggregate $visitingCareForPwsdAggregate;

    private DwsBillingStatement $statement;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->homeHelpServiceItems = [
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111263'), // 身体深2.0
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 999,
                    count: 10,
                    totalScore: 9990,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('116223'), // 家事夜1.5
                    serviceCodeCategory: DwsServiceCodeCategory::housework(),
                    unitScore: 343,
                    count: 16,
                    totalScore: 5168,
                ),
                // TODO: 居宅の明細を足す
            ];
            $self->homeHelpServiceAggregate = new DwsBillingStatementAggregate(
                serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                startedOn: Carbon::create(2008, 5, 17),
                terminatedOn: null,
                serviceDays: 26,
                subtotalScore: 15158,
                unitCost: Decimal::fromInt(112000),
                subtotalFee: 169769,
                unmanagedCopay: 16976,
                managedCopay: 16976,
                cappedCopay: 16976,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 16976,
                subtotalBenefit: 152793,
                subtotalSubsidy: null,
            );
            $self->visitingCareForPwsdItems = [
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121171'), // 重訪Ⅰ日中1.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 213,
                    count: 20,
                    totalScore: 6390,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121171'), // 重訪Ⅰ日中1.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 213,
                    count: 20,
                    totalScore: 6390,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121181'), // 重訪Ⅰ日中1.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 104,
                    count: 20,
                    totalScore: 3120,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121391'), // 重訪Ⅰ日中2.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 106,
                    count: 20,
                    totalScore: 3180,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121401'), // 重訪Ⅰ日中2.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 105,
                    count: 20,
                    totalScore: 3150,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121411'), // 重訪Ⅰ日中3.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 106,
                    count: 20,
                    totalScore: 3180,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121421'), // 重訪Ⅰ日中3.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 104,
                    count: 20,
                    totalScore: 3120,
                ),
            ];
            $self->visitingCareForPwsdAggregate = new DwsBillingStatementAggregate(
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                startedOn: Carbon::create(2008, 5, 17),
                terminatedOn: null,
                serviceDays: 20,
                subtotalScore: 14760,
                unitCost: Decimal::fromInt(112000),
                subtotalFee: 165312,
                unmanagedCopay: 16531,
                managedCopay: 16531,
                cappedCopay: 16531,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 16531,
                subtotalBenefit: 148781,
                subtotalSubsidy: null,
            );
            $self->statement = DwsBillingStatement::create([
                'id' => 1,
                'dwsBillingId' => 1,
                'dwsBillingBundleId' => 1,
                'copayLimit' => 37200,
                'totalScore' => 29918,
                'totalFee' => 335081,
                'totalCappedCopay' => 33507,
                'totalAdjustedCopay' => 33507,
                'totalCoordinatedCopay' => null,
                'totalCopay' => 33507,
                'totalBenefit' => 301574,
                'totalSubsidy' => 0,
                'aggregates' => [],
                'items' => [],
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_instance(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should(
            'return an instance when the statement contains home-help-service only',
            function (): void {
                $statement = $this->statement->copy([
                    'totalScore' => 15158,
                    'totalFee' => 169769,
                    'totalCappedCopay' => 16976,
                    'totalAdjustedCopay' => null,
                    'totalCoordinatedCopay' => null,
                    'totalCopay' => 16976,
                    'totalBenefit' => 152793,
                    'aggregates' => [
                        $this->homeHelpServiceAggregate,
                    ],
                    'items' => $this->homeHelpServiceItems,
                ]);
                $this->assertModelStrictEquals(
                    expected: UserBillingDwsItem::create([
                        'dwsStatementId' => 1,
                        'score' => 15158,
                        'unitCost' => Decimal::fromInt(112000),
                        'subtotalCost' => 169769,
                        'tax' => ConsumptionTaxRate::zero(),
                        // 16,976円 × 9,990単位 / (9,990単位 + 5,168単位) = 11,188円
                        'medicalDeductionAmount' => 11188,
                        'benefitAmount' => 152793,
                        'subsidyAmount' => 0,
                        'totalAmount' => 16976,
                        'copayWithoutTax' => 16976,
                        'copayWithTax' => 16976,
                    ]),
                    actual: UserBillingDwsItem::from($statement)
                );
            }
        );
        $this->should(
            'return an instance when the statement contains visiting-care-for-pwsd only',
            function (): void {
                $statement = $this->statement->copy([
                    'totalScore' => 14760,
                    'totalFee' => 165312,
                    'totalCappedCopay' => 16531,
                    'totalAdjustedCopay' => null,
                    'totalCoordinatedCopay' => null,
                    'totalCopay' => 16531,
                    'totalBenefit' => 148781,
                    'aggregates' => [
                        $this->visitingCareForPwsdAggregate,
                    ],
                    'items' => $this->visitingCareForPwsdItems,
                ]);
                $this->assertModelStrictEquals(
                    expected: UserBillingDwsItem::create([
                        'dwsStatementId' => 1,
                        'score' => 14760,
                        'unitCost' => Decimal::fromInt(112000),
                        'subtotalCost' => 165312,
                        'tax' => ConsumptionTaxRate::zero(),
                        // 16,531円 × 1/2 = 8,265円
                        'medicalDeductionAmount' => 8265,
                        'benefitAmount' => 148781,
                        'subsidyAmount' => 0,
                        'totalAmount' => 16531,
                        'copayWithoutTax' => 16531,
                        'copayWithTax' => 16531,
                    ]),
                    actual: UserBillingDwsItem::from($statement)
                );
            }
        );
        $this->should(
            'return an instance when the statement contains both of service divisions',
            function (): void {
                $statement = $this->statement->copy([
                    'aggregates' => [
                        $this->homeHelpServiceAggregate,
                        $this->visitingCareForPwsdAggregate,
                    ],
                    'items' => [
                        ...$this->homeHelpServiceItems,
                        ...$this->visitingCareForPwsdItems,
                    ],
                ]);
                $this->assertModelStrictEquals(
                    expected: UserBillingDwsItem::create([
                        'dwsStatementId' => 1,
                        'score' => 29918,
                        'unitCost' => Decimal::fromInt(112000),
                        'subtotalCost' => 335081,
                        'tax' => ConsumptionTaxRate::zero(),
                        // 居宅: 16,976円 × 9,990単位 / (9,990単位 + 5,168単位) = 11,188円
                        // 重訪: 16,531円 × 1/2 = 8,265円
                        // 合計: 11,188円 ＋ 8,265円 = 19,453円
                        'medicalDeductionAmount' => 19453,
                        'benefitAmount' => 301574,
                        'subsidyAmount' => 0,
                        'totalAmount' => 33507,
                        'copayWithoutTax' => 33507,
                        'copayWithTax' => 33507,
                    ]),
                    actual: UserBillingDwsItem::from($statement)
                );
            }
        );
        $this->should(
            'return an instance when the statement contains subsidyAmount',
            function (): void {
                $statement = $this->statement->copy([
                    'totalSubsidy' => 10000,
                    'aggregates' => [
                        $this->homeHelpServiceAggregate->copy([
                            'subtotalSubsidy' => 10000,
                        ]),
                        $this->visitingCareForPwsdAggregate,
                    ],
                    'items' => [
                        ...$this->homeHelpServiceItems,
                        ...$this->visitingCareForPwsdItems,
                    ],
                ]);
                $this->assertModelStrictEquals(
                    expected: UserBillingDwsItem::create([
                        'dwsStatementId' => 1,
                        'score' => 29918,
                        'unitCost' => Decimal::fromInt(112000),
                        'subtotalCost' => 335081,
                        'tax' => ConsumptionTaxRate::zero(),
                        // 居宅: 6,976円 × 9,990単位 / (9,990単位 + 5,168単位) = 4,597円
                        // 重訪: 16,531円 × 1/2 = 8,265円
                        // 合計: 4,597円 ＋ 8,265円 = 12,862円
                        'medicalDeductionAmount' => 12862,
                        'benefitAmount' => 301574,
                        'subsidyAmount' => 10000,
                        'totalAmount' => 23507,
                        'copayWithoutTax' => 23507,
                        'copayWithTax' => 23507,
                    ]),
                    actual: UserBillingDwsItem::from($statement)
                );
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createInstance();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\UserBilling\UserBillingDwsItem
     */
    private function createInstance(array $attrs = []): UserBillingDwsItem
    {
        $x = UserBillingDwsItem::create([
            'dwsStatementId' => 1,
            'score' => 100,
            'unitCost' => 10,
            'subtotalCost' => 1000,
            'tax' => ConsumptionTaxRate::ten(),
            'medicalDeductionAmount' => 5000,
            'benefitAmount' => 2000,
            'subsidyAmount' => 1000,
            'totalAmount' => 1000,
            'copayWithoutTax' => 2000,
            'copayWithTax' => 2200,
        ]);
        return $x->copy($attrs);
    }
}
