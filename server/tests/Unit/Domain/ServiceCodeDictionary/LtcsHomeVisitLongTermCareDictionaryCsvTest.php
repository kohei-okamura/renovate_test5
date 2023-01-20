<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow;
use Lib\Csv;
use ScalikePHP\Seq;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv} のテスト.
 */
final class LtcsHomeVisitLongTermCareDictionaryCsvTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $actual = $this->createInstance();
            $this->assertInstanceOf(LtcsHomeVisitLongTermCareDictionaryCsv::class, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_rows(): void
    {
        $this->should('return an array of LtcsHomeVisitLongTermCareDictionaryCsvRow as Seq', function (): void {
            $instance = $this->createInstance();
            $actual = $instance->rows();
            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof LtcsHomeVisitLongTermCareDictionaryCsvRow);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv
     */
    private function createInstance(): LtcsHomeVisitLongTermCareDictionaryCsv
    {
        $path = codecept_data_dir('ServiceCodeDictionary/ltcs-home-visit-long-term-care-dictionary-csv-test.csv');
        $csv = Csv::read($path);
        return LtcsHomeVisitLongTermCareDictionaryCsv::create($csv);
    }
}
