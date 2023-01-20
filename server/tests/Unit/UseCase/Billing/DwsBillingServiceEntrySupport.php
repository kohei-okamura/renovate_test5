<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv;
use Lib\Csv;

/**
 * サービスコード辞書エントリ一覧の生成処理
 *
 * 二箇所で使うので共通化したがこの処理が重いためいろんなテストで呼び出すとやばい
 * エントリの取得処理を最適化するまでの一時的なものとして使う想定
 */
trait DwsBillingServiceEntrySupport
{
    /**
     * テスト用の障害福祉サービス：居宅介護：サービスコード辞書(令和3年度4月改訂対応用）エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|iterable
     */
    private function entriesAfterApril2021(): iterable
    {
        $id = 1;
        // テストが重いので改善するまでは最低限に減らしたエントリ一覧を使っておく
        $csv = codecept_data_dir('ServiceCodeDictionary/dws-home-help-service-dictionary-2021-04-csv-example-mini.csv');
        $data = Csv::read($csv);
        foreach (DwsHomeHelpServiceDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }
}
