<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary} のテスト.
 */
final class LtcsHomeVisitLongTermCareDictionaryTest extends Test
{
    use CarbonMixin;
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
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary
     */
    private function createInstance(array $attrs = []): LtcsHomeVisitLongTermCareDictionary
    {
        $values = [
            'id' => 517,
            'effectivatedOn' => Carbon::create(2021, 4, 1),
            'name' => '令和3年4月度改正版',
            'version' => 2,
            'createdAt' => Carbon::now()->subDay(),
            'updatedAt' => Carbon::now(),
        ];
        return LtcsHomeVisitLongTermCareDictionary::create($attrs + $values);
    }
}
