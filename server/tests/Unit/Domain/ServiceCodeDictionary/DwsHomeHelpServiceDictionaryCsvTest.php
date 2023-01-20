<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsvRow;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv} のテスト.
 */
final class DwsHomeHelpServiceDictionaryCsvTest extends Test
{
    use UnitSupport;

    private const FILEPATH = 'ServiceCodeDictionary/dws-home-help-dictionary.csv';

    private DwsHomeHelpServiceDictionaryCsv $csv;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->csv = DwsHomeHelpServiceDictionaryCsv::create(Csv::read(codecept_data_dir(self::FILEPATH)));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_rows(): void
    {
        $this->should(
            'return Seq of DwsHomeHelpServiceDictionaryRow corresponding to lines between startRow and endRow',
            function () {
                $expected = Csv::read(codecept_data_dir(self::FILEPATH))
                    ->map(function (array $row): DwsHomeHelpServiceDictionaryCsvRow {
                        return DwsHomeHelpServiceDictionaryCsvRow::create($row);
                    });

                $actual = $this->csv->rows();

                $this->assertEquals($expected->toArray(), $actual->toArray());
            }
        );
    }
}
