<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Contract;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Contract\Contract} のテスト
 */
final class ContractTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

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
     * @return \Domain\Contract\Contract
     */
    private function createInstance(array $attrs = []): Contract
    {
        $values = [
            'id' => 1,
            'organizationId' => 2,
            'userId' => 3,
            'officeId' => 4,
            'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
            'status' => ContractStatus::terminated(),
            'contractedOn' => Carbon::create(1996, 6, 4),
            'terminatedOn' => Carbon::create(2008, 5, 17),
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                    'start' => Carbon::create(2021, 1, 1),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
            ],
            'ltcsPeriod' => ContractPeriod::create([
                'start' => Carbon::create(2019, 1, 1),
                'end' => Carbon::create(2019, 12, 31),
            ]),
            'expiredReason' => LtcsExpiredReason::hospitalized(),
            'note' => 'だるまさんがころんだ',
        ];
        return Contract::create($attrs + $values);
    }
}
