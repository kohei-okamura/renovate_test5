<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingFile;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Faker\Faker;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBilling} のテスト.
 */
final class LtcsBillingTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
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
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBilling
     */
    private function createInstance(array $attrs = []): LtcsBilling
    {
        $faker = Faker::make(2097528240);
        $values = [
            'organizationId' => $faker->randomNumber(4, true),
            'office' => new LtcsBillingOffice(
                officeId: $faker->randomNumber(4, true),
                code: (string)$faker->numberBetween(1000000000, 2000000000),
                name: $faker->company,
                abbr: $faker->company,
                addr: $faker->addr,
                tel: $faker->phoneNumber,
            ),
            'transactedIn' => Carbon::parse($faker->date('2021-04-01')),
            'files' => [
                new LtcsBillingFile(
                    name: '介護給付費請求書・明細書_新宿_202012.csv',
                    path: 'attachments/xyz.csv',
                    token: str_repeat('x', 60),
                    mimeType: MimeType::csv(),
                    createdAt: Carbon::instance($faker->dateTime('2021-04-01')),
                    downloadedAt: Carbon::instance($faker->dateTime('2021-04-01')),
                ),
            ],
            'status' => $faker->randomElement(LtcsBillingStatus::all()),
            'fixedAt' => Carbon::instance($faker->dateTime('2021-04-01')),
            'createdAt' => Carbon::instance($faker->dateTime('2021-04-01')),
            'updatedAt' => Carbon::instance($faker->dateTime('2021-04-01')),
        ];
        return LtcsBilling::create($attrs + $values);
    }
}
