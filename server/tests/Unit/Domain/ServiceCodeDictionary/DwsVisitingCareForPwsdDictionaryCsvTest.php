<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryCsvRow;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryCsv} のテスト.
 */
final class DwsVisitingCareForPwsdDictionaryCsvTest extends Test
{
    use UnitSupport;

    private const FILEPATH = 'ServiceCodeDictionary/dws-visiting-care-for-pwsd-dictionary.csv';

    private DwsVisitingCareForPwsdDictionaryCsv $csv;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->csv = DwsVisitingCareForPwsdDictionaryCsv::create(Csv::read(codecept_data_dir(self::FILEPATH)));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_rows(): void
    {
        $this->should(
            'return Seq of DwsVisitingCareForPwsdDictionaryRow corresponding to lines between startRow and endRow',
            function () {
                $expected = Csv::read(codecept_data_dir(self::FILEPATH))
                    ->map(function (array $row): DwsVisitingCareForPwsdDictionaryCsvRow {
                        return DwsVisitingCareForPwsdDictionaryCsvRow::create($row);
                    });

                $actual = $this->csv->rows();

                $this->assertEquals($expected->toArray(), $actual->toArray());
            }
        );
    }
}
