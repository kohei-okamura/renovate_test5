<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Office\Office;
use Domain\Office\OfficeDwsCommAccompanyService;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeLtcsCareManagementService;
use Domain\Office\OfficeLtcsCompHomeVisitingService;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeLtcsPreventionService;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Faker\Generator;

/**
 * Office Example.
 *
 * @property-read \Domain\Office\Office[] $offices
 * @mixin \Tests\Unit\Examples\OrganizationExample
 */
trait OfficeExample
{
    /**
     * Generate an example of Office.
     *
     * @param array $overwrites
     * @return \Domain\Office\Office
     */
    public function generateOffice(array $overwrites): Office
    {
        $faker = app(Generator::class);
        $name = $faker->officeName();
        $attrs = [
            'organizationId' => $this->organizations[0]->id,
            'officeGroupId' => $this->officeGroups[0]->id,
            'name' => $name['name'] . $overwrites['id'] ?? '',
            'abbr' => $name['abbr'],
            'phoneticName' => $name['phonetic_name'],
            'corporationName' => '事業所テスト',
            'phoneticCorporationName' => 'ジギョウショテスト',
            'purpose' => $faker->randomElement(Purpose::all()),
            'addr' => $faker->addr,
            'location' => Location::create([
                'lat' => $faker->randomFloat(6, -90, 90),
                'lng' => $faker->randomFloat(6, -180, 180),
            ]),
            'tel' => '01-2345-6789',
            'fax' => $faker->boolean(50) ? '01-2000-6789' : '',
            'email' => $faker->emailAddress,
            'qualifications' => [
                OfficeQualification::dwsHomeHelpService(),
                OfficeQualification::dwsOthers(),
                OfficeQualification::ltcsHomeVisitLongTermCare(),
            ],
            'dwsGenericService' => OfficeDwsGenericService::create([
                'dwsAreaGradeId' => $this->dwsAreaGrades[5]->id,
                'code' => $faker->numerify(str_repeat('#', 10)),
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'dwsCommAccompanyService' => OfficeDwsCommAccompanyService::create([
                'code' => $faker->numerify(str_repeat('#', 10)),
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'ltcsCareManagementService' => OfficeLtcsCareManagementService::create([
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[4]->id,
                'code' => $faker->numerify(str_repeat('#', 10)),
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'ltcsHomeVisitLongTermCareService' => OfficeLtcsHomeVisitLongTermCareService::create([
                'ltcsAreaGradeId' => $this->ltcsAreaGrades[4]->id,
                'code' => $faker->numerify(str_repeat('#', 10)),
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'ltcsCompHomeVisitingService' => OfficeLtcsCompHomeVisitingService::create([
                'code' => $faker->numerify(str_repeat('#', 10)),
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'ltcsPreventionService' => new OfficeLtcsPreventionService(
                code: $faker->numerify(str_repeat('#', 10)),
                openedOn: Carbon::instance($faker->dateTime)->startOfDay(),
                designationExpiredOn: Carbon::instance($faker->dateTime)->startOfDay(),
            ),
            'status' => $faker->randomElement(OfficeStatus::all()),
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return Office::create($overwrites + $attrs);
    }

    /**
     * 事業所の一覧を生成する.
     *
     * @return \Domain\Office\Office[]
     *
     * NOTE: [5]と[6]は、OfficeRepositoryのremove系のテストで使用するのでリレーションしない
     */
    protected function offices(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateOffice([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'purpose' => Purpose::internal(), // 自社
                'name' => '事業所テスト',
                'abbr' => '事テス',
                'phoneticName' => 'ジギョウショテスト',
                'addr' => new Addr(
                    postcode: $this->toZingerPostCodeFormat($faker->postcode),
                    prefecture: Prefecture::okinawa(),
                    city: $faker->city,
                    street: $faker->streetAddress,
                    apartment: $faker->streetSuffix,
                ),
                'status' => OfficeStatus::inOperation(),
                'isEnabled' => true,
            ]),
            $this->generateOffice([
                'id' => 2,
                'organizationId' => $this->organizations[1]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'name' => '事業所テスト1',
                'phoneticName' => 'ジギョウショテスト',
                'addr' => new Addr(
                    postcode: $this->toZingerPostCodeFormat($faker->postcode),
                    prefecture: Prefecture::okinawa(),
                    city: $faker->city,
                    street: $faker->streetAddress,
                    apartment: $faker->streetSuffix,
                ),
                'status' => OfficeStatus::inPreparation(),
            ]),
            $this->generateOffice([
                'id' => 3,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'name' => 'テスト事業所',
                'abbr' => 'テス事',
                'phoneticName' => 'ケンサクテスト',
                'addr' => new Addr(
                    postcode: $this->toZingerPostCodeFormat($faker->postcode),
                    prefecture: Prefecture::okinawa(),
                    city: $faker->city,
                    street: $faker->streetAddress,
                    apartment: $faker->streetSuffix,
                ),
            ]),
            $this->generateOffice([
                'id' => 4,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'name' => '事業所テスト2',
                'phoneticName' => 'ジギョウショテスト2',
                'addr' => new Addr(
                    postcode: $this->toZingerPostCodeFormat($faker->postcode),
                    prefecture: Prefecture::tokyo(),
                    city: $faker->city,
                    street: $faker->streetAddress,
                    apartment: $faker->streetSuffix,
                ),
            ]),
            $this->generateOffice([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'ltcsPreventionService' => new OfficeLtcsPreventionService(
                    code: '1201249993', // 地域包括支援センター
                    openedOn: Carbon::instance($faker->dateTime)->startOfDay(),
                    designationExpiredOn: Carbon::instance($faker->dateTime)->startOfDay(),
                ),
            ]),
            $this->generateOffice([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 7,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 8,
                'organizationId' => $this->organizations[1]->id,
                'officeGroupId' => $this->officeGroups[1]->id,
            ]),
            $this->generateOffice([
                'id' => 9,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 10,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 11,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 12,
                'organizationId' => $this->organizations[1]->id,
                'officeGroupId' => $this->officeGroups[1]->id,
            ]),
            $this->generateOffice([
                'id' => 13,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 14,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 15,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 16,
                'organizationId' => $this->organizations[1]->id,
                'officeGroupId' => $this->officeGroups[1]->id,
            ]),
            $this->generateOffice([
                'id' => 17,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 18,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 19,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
            ]),
            $this->generateOffice([
                'id' => 20,
                'organizationId' => $this->organizations[1]->id,
                'officeGroupId' => $this->officeGroups[1]->id,
            ]),
            $this->generateOffice([
                'id' => 21,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[1]->id,
                'name' => 'ケアマネ他社事業所',
                'phoneticName' => 'ケアマネタシャジギョウショ',
                'ltcsCareManagementService' => OfficeLtcsCareManagementService::create([
                    'ltcsAreaGradeId' => $this->ltcsAreaGrades[4]->id,
                    'code' => '9876543210',
                    'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                    'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                ]),
            ]),
            $this->generateOffice([
                'id' => 22,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'qualifications' => [
                    OfficeQualification::dwsVisitingCareForPwsd(),
                ],
            ]),
            $this->generateOffice([
                'id' => 23,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'qualifications' => [
                    OfficeQualification::ltcsCareManagement(),
                ],
            ]),
            $this->generateOffice([
                'id' => 24,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'dwsGenericService' => null,
            ]),
            $this->generateOffice([
                'id' => 25,
                'organizationId' => $this->organizations[0]->id,
                'officeGroupId' => $this->officeGroups[0]->id,
                'name' => '固定値事業所',
                'corporationName' => '固定値法人',
                'addr' => new Addr(
                    postcode: '214-0038',
                    prefecture: Prefecture::kanagawa(),
                    city: '多摩区',
                    street: 'どこでもいい',
                    apartment: '存在しない建物',
                ),
                'dwsGenericService' => null,
            ]),
            // 算定情報のソート確認に使用している事業所
            $this->generateOffice([
                'id' => 26,
                'name' => '算定情報がたくさんある事業所',
                'corporationName' => '算定情報がたくさんある法人',
            ]),
        ];
    }

    /**
     * 郵便番号をZingerの形式へ変換据える
     *
     * @param string $postcode fakerが生成する郵便番号
     * @return string XXX-YYYY形式
     */
    private function toZingerPostCodeFormat(string $postcode): string
    {
        if (strlen($postcode)) {
            $postcode = substr($postcode, 0, 3) . '-' . substr($postcode, 3);
        }
        return $postcode;
    }
}
