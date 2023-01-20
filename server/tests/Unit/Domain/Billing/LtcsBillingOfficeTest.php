<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingOffice;
use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Faker\Faker;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingOffice} のテスト.
 */
final class LtcsBillingOfficeTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private const SEED = 1338739313;

    /**
     * @test
     * @return void
     */
    public function describe_constructor(): void
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
        $this->should('return an instance', function (): void {
            // 初期化前に計算しておくことで乱数のぶれをなくす
            assert($this->examples->dwsAreaGrades !== null);
            assert($this->examples->ltcsAreaGrades !== null);

            Faker::seed(self::SEED);
            $office = $this->examples->generateOffice([
                'id' => 1,
                'organizationId' => 2,
                'officeGroupId' => 3,
            ]);
            $x = LtcsBillingOffice::from($office);

            $this->assertMatchesModelSnapshot($x);
        });
        $this->should('return an instance when LtcsCareManagementService is null', function (): void {
            // 初期化前に計算しておくことで乱数のぶれをなくす
            assert($this->examples->dwsAreaGrades !== null);
            assert($this->examples->ltcsAreaGrades !== null);

            Faker::seed(self::SEED);
            $office = $this->examples->generateOffice([
                'id' => 1,
                'organizationId' => 2,
                'officeGroupId' => 3,
                'ltcsCareManagementService' => null,
            ]);
            $x = LtcsBillingOffice::from($office);

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
            $this->assertMatchesJsonSnapshot($x->toJson());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingOffice
     */
    private function createInstance(array $attrs = []): LtcsBillingOffice
    {
        $x = new LtcsBillingOffice(
            officeId: 3479,
            code: '1783152530',
            name: '株式会社 加納',
            abbr: '株式会社 渚',
            addr: new Addr(
                postcode: '739-0604',
                prefecture: Prefecture::hiroshima(),
                city: '大竹市',
                street: '北栄1-13-11',
                apartment: '北栄荘411',
            ),
            tel: '090-3169-6661',
        );
        return $x->copy($attrs);
    }
}
