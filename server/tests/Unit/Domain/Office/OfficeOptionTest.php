<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\Addr;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Office\Office;
use Domain\Office\OfficeOption;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Faker\Faker;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\OfficeOption} のテスト.
 */
final class OfficeOptionTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $actual = $this->createOfficeOption();
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            $office = $this->createOffice();
            $actual = OfficeOption::from($office);
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $actual = $this->createOfficeOption();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Office\OfficeOption
     */
    private function createOfficeOption(array $attrs = []): OfficeOption
    {
        $faker = Faker::make(2097528240);
        $values = [
            'keyword' => 'テスト キーワード',
            'text' => 'テスト事業所',
            'value' => $faker->randomNumber(),
        ];
        return OfficeOption::create($attrs + $values);
    }

    /**
     * テスト対象の事業所モデルを生成する.
     *
     * @param array $attrs
     * @return \Domain\Office\Office
     */
    private function createOffice(array $attrs = []): Office
    {
        $faker = Faker::make(2097528240);
        $values = [
            'id' => $faker->randomNumber(),
            'organizationId' => $faker->randomNumber(),
            'name' => 'テスト用訪問介護事業所 中野坂上',
            'abbr' => '中野坂上',
            'phoneticName' => 'テストヨウホウモンカイゴジギョウショナカノサカウエ',
            'corporationName' => '株式会社テスト介護',
            'phoneticCorporationName' => 'テストカイゴ',
            'purpose' => Purpose::external(),
            'addr' => new Addr(
                postcode: '164-0012',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: '本町1-32-2',
                apartment: 'ハーモニータワー1801',
            ),
            'location' => Location::create(['lat' => 0.0, 'lng' => 0.0]),
            'tel' => $faker->phoneNumber,
            'fax' => $faker->phoneNumber,
            'email' => $faker->emailAddress,
            'qualifications' => [],
            'officeGroupId' => $faker->randomNumber(),
            'dwsGenericService' => null,
            'dwsCommAccompanyService' => null,
            'ltcsCareManagementService' => null,
            'ltcsHomeVisitLongTermCareService' => null,
            'ltcsCompHomeVisitingService' => null,
            'status' => OfficeStatus::inOperation(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => $faker->dateTime,
            'updatedAt' => $faker->dateTime,
        ];
        return Office::create($attrs + $values);
    }
}
