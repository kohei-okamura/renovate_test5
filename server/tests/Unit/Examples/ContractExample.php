<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Faker\Generator as FakerGenerator;

/**
 * Contract Example.
 *
 * @property-read Contract[] $contracts
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait ContractExample
{
    /**
     * 契約の一覧を生成する.
     *
     * @return \Domain\Contract\Contract[]
     */
    protected function contracts(): array
    {
        $faker = app(FakerGenerator::class);
        $organizations = $this->organizations;
        $users = $this->users;
        $offices = $this->offices;
        return [
            $this->generateContract($faker, [
                'id' => 1,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::now()->startOfDay(),
            ]),
            $this->generateContract($faker, [
                'id' => 2,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'contractedOn' => Carbon::now()->addMonth()->startOfDay(),
            ]),
            $this->generateContract($faker, [
                'id' => 3,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'status' => ContractStatus::formal(),
            ]),
            $this->generateContract($faker, [
                'id' => 4,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[1]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
            ]),
            $this->generateContract($faker, [
                'id' => 5,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[2]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
            ]),
            $this->generateContract($faker, [
                'id' => 6,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[2]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'contractedOn' => Carbon::now()->startOfDay(),
            ]),
            $this->generateContract($faker, [
                'id' => 7,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'status' => ContractStatus::formal(),
            ]),
            $this->generateContract($faker, [
                'id' => 8,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[2]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::provisional(),
                'contractedOn' => Carbon::now()->addDay()->startOfDay(),
            ]),
            $this->generateContract($faker, [
                'id' => 9,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::yesterday()->startOfDay(),
            ]),
            $this->generateContract($faker, [
                'id' => 10,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::terminated(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'terminatedOn' => Carbon::parse('2020-08-15'), // 請求テスト用
            ]),
            $this->generateContract($faker, [
                'id' => 11,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[3]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::terminated(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'terminatedOn' => Carbon::parse('2020-08-15'),
            ]),
            $this->generateContract($faker, [
                'id' => 12,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[4]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::provisional(),
                'serviceSegment' => ServiceSegment::longTermCare(),
                'terminatedOn' => Carbon::create(2030, 1, 1),
            ]),
            $this->generateContract($faker, [
                'id' => 13,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[4]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::provisional(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'terminatedOn' => Carbon::create(2030, 1, 1),
            ]),
            $this->generateContract($faker, [
                'id' => 14,
                'organizationId' => $organizations[1]->id,
                'userId' => $users[4]->id,
                'officeId' => $offices[0]->id,
                'contractedOn' => Carbon::now()->subMonth()->startOfDay(),
            ]),
            $this->generateContract($faker, [
                'id' => 15,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[3]->id,
                'officeId' => $offices[3]->id,
                'contractedOn' => Carbon::create(2020, 1, 1),
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
            ]),
            $this->generateContract($faker, [
                'id' => 16,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[1]->id,
                'officeId' => $offices[3]->id,
                'contractedOn' => Carbon::create(2020, 1, 1),
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::longTermCare(),
            ]),
            $this->generateContract($faker, [
                'id' => 17,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2021, 1, 1),
                'terminatedOn' => null,
                'dwsPeriods' => [
                    DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                        'start' => Carbon::create(2020, 1, 1),
                        'end' => Carbon::create(2021, 10, 31),
                    ]),
                    DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                        'start' => Carbon::create(2020, 1, 1),
                        'end' => Carbon::create(2021, 10, 31),
                    ]),
                ],
            ]),
            $this->generateContract($faker, [
                'id' => 18,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[3]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2021, 1, 12),
                'terminatedOn' => null,
                'dwsPeriods' => [
                    DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                        'start' => Carbon::create(2020, 1, 1),
                        'end' => Carbon::create(2021, 10, 31),
                    ]),
                    DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                        'start' => Carbon::create(2020, 1, 1),
                        'end' => Carbon::create(2021, 10, 31),
                    ]),
                ],
            ]),
            $this->generateContract($faker, [
                'id' => 19,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[4]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::longTermCare(),
                'contractedOn' => Carbon::create(2021, 1, 13),
                'terminatedOn' => null,
            ]),
            $this->generateContract($faker, [
                'id' => 20,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[5]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2021, 1, 14),
                'terminatedOn' => null,
            ]),
            $this->generateContract($faker, [
                'id' => 21,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::longTermCare(),
                'contractedOn' => Carbon::create(2008, 5, 17),
                'terminatedOn' => null,
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::create(2008, 6, 1),
                    'end' => null,
                ]),
            ]),
            $this->generateContract($faker, [
                'id' => 22,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::parse('2020-08-15'),
                'terminatedOn' => Carbon::parse('2021-02-15'),
            ]),
            // 契約重複バリデーションテスト用
            $this->generateContract($faker, [
                'id' => 23,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[9]->id,
                'officeId' => $this->offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::parse('2021-01-01'),
                'terminatedOn' => Carbon::parse('2021-02-01'),
                'ltcsPeriod' => ContractPeriod::create([]),
                'expiredReason' => LtcsExpiredReason::unspecified(),
            ]),
            // 契約重複バリデーションテスト用
            $this->generateContract($faker, [
                'id' => 24,
                'organizationId' => $this->organizations[0]->id,
                'userId' => $this->users[9]->id,
                'officeId' => $this->offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::parse('2021-03-01'),
                'terminatedOn' => Carbon::parse('2021-04-01'),
                'ltcsPeriod' => ContractPeriod::create([]),
                'expiredReason' => LtcsExpiredReason::unspecified(),
            ]),
            $this->generateContract($faker, [
                'id' => 25,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[4]->id,
                'officeId' => $offices[3]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2021, 4, 1),
                'terminatedOn' => null,
            ]),
            $this->generateContract($faker, [
                'id' => 26,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[2]->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'status' => ContractStatus::formal(),
                'contractedOn' => Carbon::create(2020, 9, 1),
                'terminatedOn' => null,
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::create(2020, 10, 1),
                    'end' => null,
                ]),
            ]),
            $this->generateContract($faker, [
                'id' => 27,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[3]->id,
                'officeId' => $offices[2]->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'status' => ContractStatus::formal(),
                'contractedOn' => Carbon::create(2020, 9, 1),
                'terminatedOn' => null,
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::create(2020, 9, 1),
                    'end' => Carbon::create(2021, 2, 28),
                ]),
            ]),
            $this->generateContract($faker, [
                'id' => 28,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[0]->id,
                'officeId' => $offices[2]->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'status' => ContractStatus::formal(),
                'contractedOn' => Carbon::create(2020, 9, 1),
                'terminatedOn' => null,
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::create(2020, 10, 1),
                    'end' => null,
                ]),
            ]),
            $this->generateContract($faker, [
                'id' => 29,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[2]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2020, 4, 1),
                'terminatedOn' => null,
            ]),
            $this->generateContract($faker, [
                'id' => 30,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[3]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2021, 3, 1),
                'terminatedOn' => null,
            ]),
            $this->generateContract($faker, [
                'id' => 31,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[15]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(1000, 1, 15),
                'terminatedOn' => null,
            ]),
            // 障害福祉サービス提供期間 居宅の初回サービス提供日が入っていないデータ
            $this->generateContract($faker, [
                'id' => 32,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[3]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2018, 4, 1),
                'terminatedOn' => Carbon::create(2018, 4, 2),
                'dwsPeriods' => [
                    DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                        'start' => null,
                        'end' => null,
                    ]),
                    DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                        'start' => Carbon::instance($faker->dateTime)->startOfDay(),
                        'end' => null,
                    ]),
                ],
            ]),
            // 介護保険サービス提供期間 初回サービス提供日が入っていないデータ
            $this->generateContract($faker, [
                'id' => 33,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[4]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::longTermCare(),
                'contractedOn' => Carbon::now()->startOfDay(),
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => null,
                    'end' => null,
                ]),
            ]),
            // 障害福祉サービス提供期間 重訪の初回サービス提供日が入っていないデータ
            $this->generateContract($faker, [
                'id' => 34,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[2]->id,
                'officeId' => $offices[2]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::now()->startOfDay(),
                'dwsPeriods' => [
                    DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                        'start' => Carbon::instance($faker->dateTime)->startOfDay(),
                        'end' => null,
                    ]),
                    DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                        'start' => null,
                        'end' => null,
                    ]),
                ],
            ]),
            // 障害福祉サービス：請求：明細書の id: 20、利用者負担上限額管理結果票の id: 8 と一緒に使っている
            $this->generateContract($faker, [
                'id' => 35,
                'organizationId' => $organizations[1]->id,
                'userId' => $users[17]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(1000, 1, 15),
                'terminatedOn' => null,
            ]),
            $this->generateContract($faker, [
                'id' => 36,
                'organizationId' => $organizations[1]->id,
                'userId' => $users[17]->id,
                'officeId' => $offices[1]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::longTermCare(),
                'contractedOn' => Carbon::create(2008, 5, 17),
            ]),
            $this->generateContract($faker, [
                'id' => 37,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[19]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'contractedOn' => Carbon::create(2018, 1, 1),
                'terminatedOn' => Carbon::create(2030, 1, 1),
            ]),
            $this->generateContract($faker, [
                'id' => 38,
                'organizationId' => $organizations[0]->id,
                'userId' => $users[19]->id,
                'officeId' => $offices[0]->id,
                'status' => ContractStatus::formal(),
                'serviceSegment' => ServiceSegment::longTermCare(),
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::create(2018, 1, 1),
                    'end' => Carbon::create(2030, 1, 1),
                ]),
                'contractedOn' => Carbon::create(2018, 1, 1),
                'terminatedOn' => Carbon::create(2030, 1, 1),
            ]),
        ];
    }

    /**
     * Generate an example of Contract.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\Contract\Contract
     */
    protected function generateContract(FakerGenerator $faker, array $overwrites): Contract
    {
        $contractedOn = Carbon::instance($faker->dateTime)->startOfDay();
        $attrs = [
            'serviceSegment' => $faker->randomElement(ServiceSegment::all()),
            'status' => $faker->randomElement(ContractStatus::all()),
            'contractedOn' => $contractedOn,
            'terminatedOn' => $contractedOn->addDays($faker->numberBetween(0, 10000)),
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                    'start' => Carbon::instance($faker->dateTime)->startOfDay(),
                    'end' => Carbon::instance($faker->dateTime)->startOfDay(),
                ]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                    'start' => Carbon::instance($faker->dateTime)->startOfDay(),
                    'end' => Carbon::instance($faker->dateTime)->startOfDay(),
                ]),
            ],
            'ltcsPeriod' => ContractPeriod::create([
                'start' => Carbon::instance($faker->dateTime)->startOfDay(),
                'end' => Carbon::instance($faker->dateTime)->startOfDay(),
            ]),
            'expiredReason' => LtcsExpiredReason::hospitalized(),
            'note' => 'だるまさんがころんだ',
            'isEnabled' => true,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return Contract::create($overwrites + $attrs, true);
    }
}
