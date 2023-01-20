<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsCalcCycle;
use Domain\ServiceCodeDictionary\LtcsCalcScore;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsCalcScore} のテスト.
 */
final class LtcsCalcScoreTest extends Test
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
     * @return \Domain\ServiceCodeDictionary\LtcsCalcScore
     */
    private function createInstance(array $attrs = []): LtcsCalcScore
    {
        $values = [
            'value' => 249,
            'calcType' => LtcsCalcType::score(),
            'calcCycle' => LtcsCalcCycle::perService(),
        ];
        return LtcsCalcScore::create($attrs + $values);
    }
}
