<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryCsv;
use Lib\Csv;

/**
 * サービスコード辞書エントリ一覧の生成処理
 */
trait DwsVisitingCareForPwsdDictionaryEntrySupport
{
    /**
     * テスト用の障害福祉サービス：重度訪問介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|iterable
     */
    private function entries(): iterable
    {
        $id = 1;
        $csv = codecept_data_dir('ServiceCodeDictionary/dws-visiting-care-for-pwsd-dictionary-csv-example.csv');
        $data = Csv::read($csv);
        foreach (DwsVisitingCareForPwsdDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }

    /**
     * テスト用の障害福祉サービス：重度訪問介護：サービスコード辞書(令和3年度4月改訂対応用）エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|iterable
     */
    private function entriesAfterApril2021(): iterable
    {
        $id = 1;
        $csv = codecept_data_dir('ServiceCodeDictionary/dws-visiting-care-for-pwsd-dictionary-2021-04-csv-example.csv');
        $data = Csv::read($csv);
        foreach (DwsVisitingCareForPwsdDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }
}
