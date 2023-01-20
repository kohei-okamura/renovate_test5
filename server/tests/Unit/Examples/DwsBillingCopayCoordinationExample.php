<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Faker\Generator;

/**
 * DwsBillingCopayCoordination Examples.
 *
 * @property-read \Domain\Billing\DwsBillingCopayCoordination[] $dwsBillingCopayCoordinations
 * @mixin \Tests\Unit\Examples\DwsAreaGradeExample
 * @mixin \Tests\Unit\Examples\DwsBillingBundleExample
 * @mixin \Tests\Unit\Examples\DwsCertificationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsBillingCopayCoordinationExample
{
    /**
     * 障害福祉サービス利用者負担上限額管理結果票の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingCopayCoordination[]
     */
    protected function dwsBillingCopayCoordinations(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        return [
            $this->generateDwsBillingCopayCoordination([
                'id' => 1,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
            ]),
            $this->generateDwsBillingCopayCoordination([
                'id' => 2,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[3]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(0, 37200),
                ]),
            ]),
            $this->generateDwsBillingCopayCoordination([
                'id' => 3,
                'dwsBillingId' => $this->dwsBillingBundles[1]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[1]->id,
            ]),
            $this->generateDwsBillingCopayCoordination([
                'id' => 4,
                'dwsBillingId' => $this->dwsBillingBundles[4]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[4]->id,
                'items' => [
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 1,
                        'office' => DwsBillingOffice::from($this->offices[0]),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => $faker->numberBetween(1, 999999),
                            'copay' => $faker->numberBetween(0, 37200),
                            'coordinatedCopay' => $faker->numberBetween(1, 999999),
                        ]),
                    ]),
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 2,
                        'office' => DwsBillingOffice::from($this->offices[0]),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => $faker->numberBetween(1, 999999),
                            'copay' => $faker->numberBetween(0, 37200),
                            'coordinatedCopay' => $faker->numberBetween(1, 999999),
                        ]),
                    ]),
                ],
            ]),
            $this->generateDwsBillingCopayCoordination([
                'id' => 5,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[5]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(0, 37200),
                ]),
            ]),
            $this->generateDwsBillingCopayCoordination([
                'id' => 6,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[4]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(0, 37200),
                ]),
            ]),
            $this->generateDwsBillingCopayCoordination([
                'id' => 7,
                'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
                'user' => DwsBillingUser::create([
                    'userId' => $this->users[6]->id,
                    'dwsCertificationId' => $this->dwsCertifications[9]->id,
                    'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                    'name' => StructuredName::empty(),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => $faker->numberBetween(0, 37200),
                ]),
            ]),
            // 明細書の id: 20 と一緒に使っている
            // 利用者負担上限額管理結果票の状態が「未確定」
            // 明細書の状態、上限管理区分が「入力中」（利用者負担上限額管理結果票の状態が「確定済」に更新可能な状態）
            $this->generateDwsBillingCopayCoordination([
                'id' => 8,
                'user' => $this->generateDwsBillingUserForCopayCoordination([
                    'userId' => $this->users[17]->id,
                ]),
                'status' => DwsBillingStatus::ready(),
            ]),
            // 明細書の id: 21 と一緒に使っている
            // 利用者負担上限額管理結果票が更新可能な状態
            $this->generateDwsBillingCopayCoordination([
                'id' => 9,
                'user' => $this->generateDwsBillingUserForCopayCoordination([
                    'userId' => $this->users[18]->id,
                ]),
                'dwsBillingBundleId' => $this->dwsBillingBundles[8]->id,
            ]),
        ];
    }

    /**
     * Generate an example of DwsBillingUser.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingUser
     */
    private function generateDwsBillingUserForCopayCoordination(array $overwrites): DwsBillingUser
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $name = $overwrites['name'] ?? [];
        unset($overwrites['name']);
        $childName = $overwrites['childName'] ?? [];
        unset($overwrites['childName']);
        $values = [
            'userId' => $this->users[2]->id,
            'dwsCertificationId' => $this->dwsCertifications[9]->id,
            'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
            'name' => StructuredName::empty()->copy($name),
            'childName' => StructuredName::empty()->copy($childName),
            'copayLimit' => $faker->numberBetween(0, 37200),
        ];
        return DwsBillingUser::create($overwrites + $values, true);
    }

    /**
     * Generate an example of DwsBillingCopayCoordination.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function generateDwsBillingCopayCoordination(array $overwrites)
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $values = [
            'dwsBillingId' => $this->dwsBillingBundles[0]->dwsBillingId,
            'dwsBillingBundleId' => $this->dwsBillingBundles[0]->id,
            'office' => DwsBillingOffice::from($this->offices[0]),
            'user' => DwsBillingUser::create([
                'userId' => $this->users[2]->id,
                'dwsCertificationId' => $this->dwsCertifications[9]->id,
                'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
                'name' => StructuredName::empty(),
                'childName' => StructuredName::empty(),
                'copayLimit' => $faker->numberBetween(0, 37200),
            ]),
            'result' => CopayCoordinationResult::notCoordinated(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
            'items' => [
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 1,
                    'office' => DwsBillingOffice::from($this->offices[0]),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 30000,
                        'copay' => 3000,
                        'coordinatedCopay' => 3000,
                    ]),
                ]),
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 2,
                    'office' => DwsBillingOffice::from($this->offices[0]),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 30000,
                        'copay' => 3000,
                        'coordinatedCopay' => 3000,
                    ]),
                ]),
            ],
            'total' => DwsBillingCopayCoordinationPayment::create([
                'fee' => 60000,
                'copay' => 6000,
                'coordinatedCopay' => 6000,
            ]),
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsBillingCopayCoordination::create($overwrites + $values);
    }
}
