<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Lib\Csv;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow} のテスト.
 */
final class LtcsHomeVisitLongTermCareDictionaryCsvRowTest extends Test
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
            $actual = $this->createInstances()->head();
            $this->assertInstanceOf(LtcsHomeVisitLongTermCareDictionaryCsvRow::class, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toDictionaryEntry(): void
    {
        $this->should('return an instance of LtcsHomeVisitLongTermCareDictionaryEntry', function (): void {
            $actual = $this->createInstances()->map(
                function (LtcsHomeVisitLongTermCareDictionaryCsvRow $x): LtcsHomeVisitLongTermCareDictionaryEntry {
                    return $x->toDictionaryEntry(['dictionaryId' => 517]);
                }
            );
            $this->assertMatchesModelSnapshot($actual->toArray());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow[]|\ScalikePHP\Seq
     */
    private function createInstances(): Seq
    {
        $path = codecept_data_dir('ServiceCodeDictionary/ltcs-home-visit-long-term-care-dictionary-csv-test.csv');
        return Csv::read($path)->map(function (array $values): LtcsHomeVisitLongTermCareDictionaryCsvRow {
            return LtcsHomeVisitLongTermCareDictionaryCsvRow::create($values);
        });
    }
}
