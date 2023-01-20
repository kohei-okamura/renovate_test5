<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Faker\Generator;

/**
 * DwsCertification Example.
 *
 * @property-read \Domain\DwsCertification\DwsCertification[] $dwsCertifications
 */
trait DwsCertificationExample
{
    /**
     * 障害福祉サービス受給者証の一覧を生成する.
     *
     * @return \Domain\DwsCertification\DwsCertification[]
     */
    protected function dwsCertifications(): array
    {
        return [
            $this->generateDwsCertification([
                'id' => 1,
                'userId' => 1,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-10-9')->subYear(),
                'activatedOn' => Carbon::parse('2020-10-10')->subYear(),
                'deactivatedOn' => Carbon::parse('2020-10-10')->addYear(),
            ]),
            $this->generateDwsCertification([
                'id' => 2,
                'userId' => 4,
                'effectivatedOn' => Carbon::parse('2020-10-9')->addYear(),
            ]),
            $this->generateDwsCertification([
                'id' => 3,
                'effectivatedOn' => Carbon::create(2020, 2, 3),
                'userId' => 5,
                'dwsLevel' => DwsLevel::level4(),
                'isSubjectOfComprehensiveSupport' => false,
                'grants' => [
                    $this->grant([
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        'grantedAmount' => '',
                        'activatedOn' => Carbon::create(2021, 1, 1),
                        'deactivatedOn' => Carbon::create(2021, 12, 31),
                    ]),
                ],
                'status' => DwsCertificationStatus::approved(),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::none(),
                ]),
            ]),
            $this->generateDwsCertification([
                'id' => 4,
                'effectivatedOn' => Carbon::create(2020, 2, 4),
                'userId' => 6,
            ]),
            $this->generateDwsCertification([
                'id' => 5,
                'effectivatedOn' => Carbon::create(2020, 2, 5),
                'userId' => 7,
            ]),
            $this->generateDwsCertification([
                'id' => 6,
                'effectivatedOn' => Carbon::create(2021, 2, 6),
                'userId' => 8,
            ]),
            $this->generateDwsCertification([
                'id' => 7,
                'effectivatedOn' => Carbon::create(2021, 2, 7),
                'userId' => 9,
            ]),
            $this->generateDwsCertification([
                'id' => 8,
                'effectivatedOn' => Carbon::create(2021, 2, 8),
                'userId' => 10,
            ]),
            $this->generateDwsCertification([
                'id' => 9,
                'effectivatedOn' => Carbon::create(2021, 2, 9),
                'userId' => 11,
            ]),
            $this->generateDwsCertification([
                'id' => 10,
                'effectivatedOn' => Carbon::create(2021, 2, 10),
                'userId' => 12,
                'grants' => [$this->grant(), $this->grant(), $this->grant(), $this->grant()],
                'agreements' => [$this->agreement(), $this->agreement(), $this->agreement(), $this->agreement()],
            ]),
            $this->generateDwsCertification([
                'id' => 11, // 重訪Ⅲ
                'effectivatedOn' => Carbon::create(2021, 1, 11),
                'userId' => $this->users[3]->id,
                'activatedOn' => Carbon::parse('2021-01-01'),
                'dwsLevel' => DwsLevel::level5(),
                'grants' => [
                    $this->grant([
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        'grantedAmount' => '',
                        'activatedOn' => Carbon::create(2021, 1, 1),
                        'deactivatedOn' => Carbon::create(2021, 12, 31),
                    ]),
                ],
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'indexNumber' => 1,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 1, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                    DwsCertificationAgreement::create([
                        'indexNumber' => 2,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 1, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                ],
                'copayLimit' => 37200,
                'status' => DwsCertificationStatus::approved(),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::external(),
                    'officeId' => $this->offices[3]->id,
                ]),
                'isSubjectOfComprehensiveSupport' => false,
            ]),
            $this->generateDwsCertification([
                'id' => 12,
                'effectivatedOn' => Carbon::create(2021, 2, 12),
                'userId' => $this->users[3]->id,
                'activatedOn' => Carbon::parse('2020-12-01'),
            ]),
            $this->generateDwsCertification([
                'id' => 13,
                'effectivatedOn' => Carbon::create(2021, 2, 13),
                'userId' => $this->users[3]->id,
                'activatedOn' => Carbon::parse('2020-09-01'),
                'deactivatedOn' => Carbon::parse('2020-12-01'),
            ]),
            $this->generateDwsCertification([
                'id' => 14,
                'effectivatedOn' => Carbon::create(2021, 2, 14),
                'userId' => $this->users[3]->id,
                'deactivatedOn' => Carbon::parse('2020-09-01'),
            ]),
            $this->generateDwsCertification([
                'id' => 15,
                'effectivatedOn' => Carbon::create(2021, 2, 15),
                'userId' => $this->users[2]->id,
                'status' => DwsCertificationStatus::approved(),
                'activatedOn' => Carbon::parse('2020-09-01'),
                'deactivatedOn' => Carbon::parse('2021-09-01'),
            ]),
            $this->generateDwsCertification([
                'id' => 16,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2021-01-01'),
                'activatedOn' => Carbon::parse('2021-01-01'),
                'deactivatedOn' => Carbon::parse('2021-12-31'),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::none(), // 上限管理なし
                ]),
                'agreements' => [
                    $this->agreement([
                        'officeId' => $this->offices[0]->id,
                        'agreedOn' => Carbon::parse('2010-01-01'),
                    ]),
                ],
            ]),
            $this->generateDwsCertification([
                'id' => 17,
                'effectivatedOn' => Carbon::create(2021, 1, 4),
                'userId' => $this->users[5]->id,
                'status' => DwsCertificationStatus::approved(),
                'isSubjectOfComprehensiveSupport' => true,
            ]),
            $this->generateDwsCertification([
                'id' => 18, // 重訪Ⅲ
                'effectivatedOn' => Carbon::create(2021, 4, 1),
                'userId' => $this->users[3]->id,
                'activatedOn' => Carbon::create(2021, 4, 1),
                'dwsLevel' => DwsLevel::level5(),
                'grants' => [
                    DwsCertificationGrant::create([
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        'grantedAmount' => '',
                        'activatedOn' => Carbon::create(2021, 4, 1),
                        'deactivatedOn' => Carbon::create(2022, 12, 31),
                    ]),
                ],
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'indexNumber' => 1,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 4, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                    DwsCertificationAgreement::create([
                        'indexNumber' => 2,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 4, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                ],
                'copayLimit' => 37200,
                'status' => DwsCertificationStatus::approved(),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::external(),
                    'officeId' => $this->offices[3]->id,
                ]),
                'isSubjectOfComprehensiveSupport' => false,
            ]),
            $this->generateDwsCertification([
                'id' => 19,
                'userId' => 3,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-10-8')->subYear(),
                'activatedOn' => Carbon::parse('2020-10-10')->subYear(),
                'deactivatedOn' => Carbon::parse('2020-10-10')->addYear(),
            ]),
            $this->generateDwsCertification([
                'id' => 20,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2021-05-01'),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::external(),
                    'officeId' => $this->offices[3]->id,
                ]),
                'agreements' => [$this->agreement()],
            ]),
            $this->generateDwsCertification([
                'id' => 21, // 重訪Ⅲ
                'effectivatedOn' => Carbon::create(2021, 4, 30),
                'userId' => $this->users[3]->id,
                'activatedOn' => Carbon::parse('2021-05-01'),
                'dwsLevel' => DwsLevel::level5(),
                'grants' => [
                    DwsCertificationGrant::create([
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        'grantedAmount' => '',
                        'activatedOn' => Carbon::create(2021, 4, 30),
                        'deactivatedOn' => Carbon::create(2022, 12, 31),
                    ]),
                ],
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'indexNumber' => 1,
                        'officeId' => $this->offices[0]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 4, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                    DwsCertificationAgreement::create([
                        'indexNumber' => 2,
                        'officeId' => $this->offices[0]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 4, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                ],
                'copayLimit' => 37200,
                'status' => DwsCertificationStatus::approved(),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::external(),
                    'officeId' => $this->offices[3]->id,
                ]),
                'isSubjectOfComprehensiveSupport' => false,
                'deactivatedOn' => Carbon::create(2022, 9, 30),
            ]),
            $this->generateDwsCertification([
                'id' => 22,
                'userId' => 1,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-10-7')->subYear(),
                'activatedOn' => Carbon::parse('2020-10-10')->subYear(),
                'deactivatedOn' => Carbon::parse('2020-10-10')->addYear(),
                'grants' => [DwsCertificationGrant::create([
                    'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                    'grantedAmount' => 'test',
                    'activatedOn' => Carbon::create(2020, 10, 9),
                    'deactivatedOn' => Carbon::create(2021, 10, 9),
                ])],
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'indexNumber' => 1,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2020, 10, 9),
                        'expiredOn' => null,
                    ]),
                ],
            ]),
            // 自社事業所で上限管理を行う
            $this->generateDwsCertification([
                'id' => 23,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-10-15'),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::internal(),
                    'officeId' => $this->offices[0]->id,
                ]),
            ]),
            // 自社事業所で上限管理を行わない（既存でも external はあるが、分かりやすいように再定義）
            $this->generateDwsCertification([
                'id' => 24,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-10-16'),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::external(),
                    'officeId' => $this->offices[0]->id,
                ]),
            ]),
            // 利用者負担上限額管理結果票の id: 8 と一緒に使っている
            // id: 18 のユーザーに有効な証明書をつけたいだけ
            $this->generateDwsCertification([
                'id' => 25,
                'userId' => $this->users[17]->id,
                'status' => DwsCertificationStatus::approved(),
            ]),
            // 地域加算用
            $this->generateDwsCertification([
                'id' => 26,
                'userId' => $this->users[19]->id,
                'dwsLevel' => DwsLevel::level6(),
                'isSubjectOfComprehensiveSupport' => true,
                'status' => DwsCertificationStatus::approved(),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::none(),
                ]),
                'grants' => [
                    DwsCertificationGrant::create([
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1(),
                        'grantedAmount' => '',
                        'activatedOn' => Carbon::create(2020, 4, 1),
                        'deactivatedOn' => Carbon::create(2030, 4, 1),
                    ]),
                ],
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'indexNumber' => 1,
                        'officeId' => $this->offices[0]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2020, 4, 1),
                        'expiredOn' => Carbon::create(2030, 4, 1),
                    ]),
                ],
                'effectivatedOn' => Carbon::create(2020, 4, 1),
                'deactivatedOn' => Carbon::create(2030, 4, 1),
            ]),
            // 福祉・介護職員等ベースアップ等支援加算
            $this->generateDwsCertification([
                'id' => 27,
                'effectivatedOn' => Carbon::create(2022, 10, 01),
                'dwsNumber' => '0123456789',
                'cityCode' => '456789',
                'cityName' => 'ベースアップ市',
                'userId' => $this->users[3]->id,
                'activatedOn' => Carbon::parse('2021-01-01'),
                'dwsLevel' => DwsLevel::level5(),
                'grants' => [
                    DwsCertificationGrant::create([
                        'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        'grantedAmount' => '',
                        'activatedOn' => Carbon::create(2021, 4, 1),
                        'deactivatedOn' => Carbon::create(2022, 12, 31),
                    ]),
                ],
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'indexNumber' => 1,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 1, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                    DwsCertificationAgreement::create([
                        'indexNumber' => 2,
                        'officeId' => $this->offices[2]->id,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                        'paymentAmount' => 10000,
                        'agreedOn' => Carbon::create(2021, 1, 1),
                        'expiredOn' => Carbon::today(),
                    ]),
                ],
                'copayLimit' => 37200,
                'status' => DwsCertificationStatus::approved(),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::external(),
                    'officeId' => $this->offices[3]->id,
                ]),
                'isSubjectOfComprehensiveSupport' => false,
            ]),
            // 福祉・介護職員等ベースアップ等支援加算
            $this->generateDwsCertification([
                'id' => 28,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedOn' => Carbon::parse('2022-10-01'),
                'activatedOn' => Carbon::parse('2022-10-01'),
                'deactivatedOn' => Carbon::parse('2025-12-31'),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::none(), // 上限管理なし
                ]),
                'agreements' => [
                    $this->agreement([
                        'officeId' => $this->offices[0]->id,
                        'agreedOn' => Carbon::parse('2010-01-01'),
                    ]),
                ],
            ]),
        ];
    }

    /**
     * 障害福祉サービス受給者証を生成する.
     *
     * @param array $overwrites
     * @return \Domain\DwsCertification\DwsCertification
     */
    protected function generateDwsCertification(array $overwrites): DwsCertification
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'userId' => $this->users[0]->id,
            'dwsLevel' => $faker->randomElement(DwsLevel::all()),
            'status' => $faker->randomElement(DwsCertificationStatus::all()),
            'dwsTypes' => $faker->unique(true)->randomElements(DwsType::all()),
            'copayCoordination' => CopayCoordination::create([
                'copayCoordinationType' => $faker->randomElement(CopayCoordinationType::all()),
                'officeId' => $this->offices[0]->id,
            ]),
            'child' => Child::create([
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
                'birthday' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'dwsNumber' => $faker->numerify(str_repeat('#', 10)),
            'cityCode' => $faker->numerify(str_repeat('#', 6)),
            'cityName' => $faker->city,
            'copayRate' => 10,
            'copayLimit' => 37200,
            'isSubjectOfComprehensiveSupport' => $faker->boolean,
            'agreements' => [$this->agreement(), $this->agreement()],
            'grants' => [$this->grant(), $this->grant()],
            'issuedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'effectivatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'activatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'deactivatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'copayActivatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'copayDeactivatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsCertification::create($overwrites + $attrs);
    }

    /**
     * DwsCertificationAgreement のフェイクデータを作成する.
     *
     * @param array $overwrites
     * @return \Domain\DwsCertification\DwsCertificationAgreement
     */
    private function agreement(array $overwrites = []): DwsCertificationAgreement
    {
        $faker = app(Generator::class);
        $values = [
            'indexNumber' => $faker->numberBetween(1, 99),
            'officeId' => $this->offices[2]->id,
            'dwsCertificationAgreementType' => $faker->randomElement(DwsCertificationAgreementType::all()),
            'paymentAmount' => $faker->randomNumber(),
            'agreedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'expiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
        ];
        return DwsCertificationAgreement::create($overwrites + $values);
    }

    /**
     * DwsCertificationGrant のフェイクデータを作成する.
     *
     * @param array $attrs
     * @return \Domain\DwsCertification\DwsCertificationGrant
     */
    private function grant(array $attrs = []): DwsCertificationGrant
    {
        $faker = app(Generator::class);
        $x = DwsCertificationGrant::create([
            'dwsCertificationServiceType' => $faker->randomElement(DwsCertificationServiceType::all()),
            'grantedAmount' => $faker->text(255),
            'activatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'deactivatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
        ]);
        return $x->copy($attrs);
    }
}
