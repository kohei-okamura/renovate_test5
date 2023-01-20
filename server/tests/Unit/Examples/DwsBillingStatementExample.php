<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\StructuredName;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;

/**
 * DwsBillingStatement Examples.
 *
 * @property-read \Domain\Billing\DwsBillingStatement[] $dwsBillingStatements
 * @mixin \Tests\Unit\Examples\DwsAreaGradeExample
 * @mixin \Tests\Unit\Examples\DwsBillingBundleExample
 * @mixin \Tests\Unit\Examples\DwsCertificationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsBillingStatementExample
{
    /**
     * 障害福祉サービス明細書の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingStatement[]
     */
    protected function dwsBillingStatements(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        return [
            $this->generateDwsBillingStatement([
                'id' => 1,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
            ]),
            $this->generateDwsBillingStatement([
                'id' => 2,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
            ]),
            $this->generateDwsBillingStatement([
                'id' => 3,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
            ]),
            $this->generateDwsBillingStatement([
                'id' => 4,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[1]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 5,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: $faker->numberBetween(1, 7),
                        subtotalScore: $faker->numberBetween(0, 10000),
                        unitCost: Decimal::fromInt($faker->numberBetween(100000, 112000)),
                        subtotalFee: $faker->numberBetween(0, 1000000),
                        unmanagedCopay: $faker->numberBetween(0, 37200),
                        managedCopay: $faker->numberBetween(0, 37200),
                        cappedCopay: $faker->numberBetween(0, 37200),
                        adjustedCopay: $faker->numberBetween(0, 37200),
                        coordinatedCopay: $faker->numberBetween(0, 37200),
                        subtotalCopay: $faker->numberBetween(0, 37200),
                        subtotalBenefit: $faker->numberBetween(0, 1000000),
                        subtotalSubsidy: $faker->numberBetween(0, 1000000),
                    ),
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: $faker->numberBetween(1, 7),
                        subtotalScore: $faker->numberBetween(0, 10000),
                        unitCost: Decimal::fromInt($faker->numberBetween(100000, 112000)),
                        subtotalFee: $faker->numberBetween(0, 1000000),
                        unmanagedCopay: $faker->numberBetween(0, 37200),
                        managedCopay: $faker->numberBetween(0, 37200),
                        cappedCopay: $faker->numberBetween(0, 37200),
                        adjustedCopay: $faker->numberBetween(0, 37200),
                        coordinatedCopay: $faker->numberBetween(0, 37200),
                        subtotalCopay: $faker->numberBetween(0, 37200),
                        subtotalBenefit: $faker->numberBetween(0, 1000000),
                        subtotalSubsidy: $faker->numberBetween(0, 1000000),
                    ),
                ],
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: $faker->numberBetween(0, 2000),
                        count: $faker->numberBetween(1, 31),
                        totalScore: $faker->numberBetween(0, 10000),
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: $faker->numberBetween(0, 2000),
                        count: $faker->numberBetween(1, 31),
                        totalScore: $faker->numberBetween(0, 10000),
                    ),
                ],
            ]),
            $this->generateDwsBillingStatement([
                'id' => 6,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 319,
                        count: 1,
                        totalScore: 319,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111113'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 1352,
                        count: 30,
                        totalScore: 40560,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                ],
                'contracts' => [
                    DwsBillingStatement::contract([
                        'dwsGrantedServiceCode' => DwsGrantedServiceCode::physicalCare(),
                        'grantedAmount' => 10000,
                        'agreedOn' => Carbon::today()->subMonth(),
                        'expiredOn' => Carbon::today(),
                        'indexNumber' => 1,
                    ]),
                    DwsBillingStatement::contract([
                        'dwsGrantedServiceCode' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                        'grantedAmount' => 10000,
                        'agreedOn' => Carbon::today()->subMonth(),
                        'expiredOn' => Carbon::today(),
                        'indexNumber' => 2,
                    ]),
                ],
            ]),
            $this->generateDwsBillingStatement([
                'id' => 7,
                'dwsBillingId' => $this->dwsBillingBundles[5]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[5]->id,
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                ],
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: $faker->numberBetween(1, 7),
                        subtotalScore: $faker->numberBetween(0, 10000),
                        unitCost: Decimal::fromInt($faker->numberBetween(100000, 112000)),
                        subtotalFee: $faker->numberBetween(0, 1000000),
                        unmanagedCopay: $faker->numberBetween(0, 37200),
                        managedCopay: $faker->numberBetween(37201, 1000000),
                        cappedCopay: 37200,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 37200,
                        subtotalBenefit: $faker->numberBetween(0, 1000000),
                        subtotalSubsidy: $faker->numberBetween(0, 1000000),
                    ),
                ],
                'status' => DwsBillingStatus::ready(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 8,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
                'copayCoordination' => null,
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                ],
            ]),
            $this->generateDwsBillingStatement([
                'id' => 9,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
                'copayCoordination' => null,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(), // 上限管理なし
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[0]->id,
                    'dwsCertificationId' => $this->dwsCertifications[15]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 10,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(), // 上限管理なし
                'status' => DwsBillingStatus::fixed(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 11,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[2]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 12,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated(),
                'status' => DwsBillingStatus::ready(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 13,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),
                'status' => DwsBillingStatus::ready(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 14,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[5]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 15,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[4]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
                'status' => DwsBillingStatus::fixed(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 16,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[6]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unclaimable(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 17,
                'dwsBillingId' => $this->dwsBillings[6]->id,
                'dwsBillingBundleId' => $this->dwsBillingBundles[5]->id,
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 2,
                        totalScore: 200,
                    ),
                ],
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: $faker->numberBetween(1, 7),
                        subtotalScore: $faker->numberBetween(0, 10000),
                        unitCost: Decimal::fromInt($faker->numberBetween(100000, 112000)),
                        subtotalFee: $faker->numberBetween(0, 1000000),
                        unmanagedCopay: $faker->numberBetween(0, 37200),
                        managedCopay: $faker->numberBetween(37201, 1000000),
                        cappedCopay: 37200,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 37200,
                        subtotalBenefit: $faker->numberBetween(0, 1000000),
                        subtotalSubsidy: $faker->numberBetween(0, 1000000),
                    ),
                ],
                'status' => DwsBillingStatus::ready(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 18,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[6]->id,
                    'dwsCertificationId' => $this->dwsCertifications[2]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unclaimable(),
            ]),
            $this->generateDwsBillingStatement([
                'id' => 19,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'totalFee' => 228175,
                'totalBenefit' => 218875,
                'totalSubsidy' => 0,
                'totalScore' => 21285,
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: $faker->numberBetween(1, 7),
                        subtotalScore: 5000,
                        unitCost: Decimal::fromInt(11_2000),
                        subtotalFee: 56000,
                        unmanagedCopay: 5600,
                        managedCopay: 5600,
                        cappedCopay: 5600,
                        adjustedCopay: 5600,
                        coordinatedCopay: 5600,
                        subtotalCopay: 5600,
                        subtotalBenefit: 50400,
                        subtotalSubsidy: 1000,
                    ),
                ],
            ]),
            // 利用者負担上限額管理結果票の id: 8 と一緒に使っている
            // 利用者負担上限額管理結果票の状態が「未確定」
            // 明細書の状態、上限管理区分が「入力中」（利用者負担上限額管理結果票の状態が「確定済」に更新可能な状態）
            $this->generateDwsBillingStatement([
                'id' => 20,
                'user' => $this->generateDwsBillingUserForStatement([
                    'userId' => $this->users[17]->id,
                ]),
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking(),
                'copayCoordination' => null,
            ]),
            // 利用者負担上限額管理結果票の id: 21 と一緒に使っている
            // 利用者負担上限額管理結果票が更新可能な状態
            $this->generateDwsBillingStatement([
                'id' => 21,
                'user' => $this->generateDwsBillingUserForStatement([
                    'userId' => $this->users[18]->id,
                ]),
                'dwsBillingBundleId' => $this->dwsBillingBundles[8]->id,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking(),
            ]),
            // 明細書状態一括更新 API で使っている
            $this->generateDwsBillingStatement([
                'id' => 22,
                'dwsBillingId' => $this->dwsBillingBundles[9]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[9]->id,
                'status' => DwsBillingStatus::ready(),
            ]),
            // 明細書状態一括更新 API で使っている
            $this->generateDwsBillingStatement([
                'id' => 23,
                'dwsBillingId' => $this->dwsBillingBundles[9]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[9]->id,
                'status' => DwsBillingStatus::ready(),
            ]),
            // 請求額が 0 円
            $this->generateDwsBillingStatement([
                'id' => 24,
                'dwsBillingId' => $this->dwsBillingBundles[10]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[10]->id,
                'copayLimit' => 0,
                'totalScore' => 3332,
                'totalFee' => 37318,
                'totalCappedCopay' => 0,
                'totalAdjustedCopay' => null,
                'totalCoordinatedCopay' => null,
                'totalCopay' => 0,
                'totalBenefit' => 37318,
                'totalSubsidy' => 0,
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 4,
                        subtotalScore: 3332,
                        unitCost: Decimal::fromInt(11_2000),
                        subtotalFee: 37318,
                        unmanagedCopay: 3731,
                        managedCopay: 0,
                        cappedCopay: 0,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 0,
                        subtotalBenefit: 37318,
                        subtotalSubsidy: 0,
                    ),
                ],
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111131'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 833,
                        count: 4,
                        totalScore: 3332,
                    ),
                ],
            ]),
            $this->generateDwsBillingStatement([
                'id' => 25,
                'dwsBillingId' => $this->dwsBillingBundles[11]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[11]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[3]->id,
                    'dwsCertificationId' => $this->dwsCertifications[10]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(),
                ]),
                'totalFee' => 0,
                'totalBenefit' => 0,
                'totalSubsidy' => 0,
                'items' => [],
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),
            ]),
        ];
    }

    /**
     * Generate an example of DwsBillingUser.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingUser
     */
    private function generateDwsBillingUserForStatement(array $overwrites): DwsBillingUser
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $name = $overwrites['name'] ?? [];
        unset($overwrites['name']);
        $childName = $overwrites['childName'] ?? [];
        unset($overwrites['childName']);
        $values = [
            'userId' => $this->users[0]->id,
            'dwsCertificationId' => $this->dwsCertifications[9]->id,
            'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
            'name' => StructuredName::empty()->copy($name),
            'childName' => StructuredName::empty()->copy($childName),
            'copayLimit' => $faker->numberBetween(),
        ];
        return DwsBillingUser::create($overwrites + $values, true);
    }

    /**
     * Generate an example of DwsBillingStatement.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingStatement
     */
    private function generateDwsBillingStatement(array $overwrites): DwsBillingStatement
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $dwsGrantedServiceCodes = DwsGrantedServiceCode::all();
        $values = [
            'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
            'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
            'subsidyCityCode' => '123456',
            'user' => DwsBillingUser::create([
                'userId' => $this->users[0]->id,
                'dwsCertificationId' => $this->dwsCertifications[9]->id,
                'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                'name' => StructuredName::empty(),
                'childName' => StructuredName::empty(),
                'copayLimit' => 37200,
            ]),
            'dwsAreaGradeName' => $this->dwsAreaGrades[15]->name,
            'dwsAreaGradeCode' => $this->dwsAreaGrades[15]->code,
            'copayLimit' => 37200,
            'totalScore' => $faker->numberBetween(0, 10000),
            'totalFee' => $faker->numberBetween(0, 1000000),
            'totalCappedCopay' => $faker->numberBetween(0, 37200),
            'totalAdjustedCopay' => $faker->numberBetween(0, 37200),
            'totalCoordinatedCopay' => $faker->numberBetween(0, 37200),
            'totalCopay' => $faker->numberBetween(0, 37200),
            'totalBenefit' => $faker->numberBetween(0, 1000000),
            'totalSubsidy' => $faker->numberBetween(0, 10000),
            'isProvided' => false,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::fulfilled(),
            'copayCoordination' => DwsBillingStatementCopayCoordination::create([
                'office' => DwsBillingOffice::from($this->offices[0]),
                'result' => $faker->randomElement(CopayCoordinationResult::all()),
                'amount' => $faker->numberBetween(0, 37200),
            ]),
            'aggregates' => [
                new DwsBillingStatementAggregate(
                    serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                    startedOn: Carbon::today()->subDay(),
                    terminatedOn: Carbon::today(),
                    serviceDays: $faker->numberBetween(1, 7),
                    subtotalScore: $faker->numberBetween(0, 10000),
                    unitCost: Decimal::fromInt($faker->numberBetween(100000, 112000)),
                    subtotalFee: $faker->numberBetween(0, 1000000),
                    unmanagedCopay: $faker->numberBetween(0, 37200),
                    managedCopay: $faker->numberBetween(0, 37200),
                    cappedCopay: $faker->numberBetween(0, 37200),
                    adjustedCopay: $faker->numberBetween(0, 37200),
                    coordinatedCopay: $faker->numberBetween(0, 37200),
                    subtotalCopay: $faker->numberBetween(0, 37200),
                    subtotalBenefit: $faker->numberBetween(0, 1000000),
                    subtotalSubsidy: $faker->numberBetween(0, 1000000),
                ),
            ],
            'contracts' => [
                DwsBillingStatement::contract([
                    'dwsGrantedServiceCode' => $faker->randomElement(array_splice($dwsGrantedServiceCodes, 1, -1)),
                    // none, comprehensiveSupport を取り除く
                    'grantedAmount' => $faker->numberBetween(0, 10000),
                    'agreedOn' => Carbon::today()->subMonth(),
                    'expiredOn' => Carbon::today(),
                    'indexNumber' => $faker->randomNumber(),
                ]),
            ],
            'items' => [
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111111'),
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: $faker->numberBetween(0, 2000),
                    count: $faker->numberBetween(1, 31),
                    totalScore: $faker->numberBetween(0, 10000),
                ),
            ],
            'status' => DwsBillingStatus::checking(),
            'fixedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsBillingStatement::create($overwrites + $values, true);
    }
}
