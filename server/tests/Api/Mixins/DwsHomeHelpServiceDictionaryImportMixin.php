<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Mixins;

use ApiTester;

/**
 * 障害福祉 居宅 辞書インポートMixin.
 */
trait DwsHomeHelpServiceDictionaryImportMixin
{
    private static bool $isImport = false;

    /**
     * 障害福祉 居宅 辞書インポート.
     *
     * @param \ApiTester $I
     */
    public function _beforeMixinDwsHomeHelpServiceDictionaryImport(ApiTester $I)
    {
        // 辞書定義
        $dictionaries = [
            ['filename' => 'dict-dws-11.csv', 'effectivatedOn' => '2021-04-01', 'note' => '令和3年4月版'],
        ];
        self::import($I, $dictionaries);
    }

    /**
     * 辞書インポートコマンドを呼ぶ.
     *
     * @param \ApiTester $I
     * @param array $dictionaries
     */
    private static function import(ApiTester $I, array $dictionaries): void
    {
        if (!self::$isImport) {
            $app = $I->getApplication();

            $envStr = $app->environment();
            $artisan = $app->basePath('../artisan');
            $env = "--env={$envStr}";
            foreach ($dictionaries as $index => $dictionary) {
                $filename = $dictionary['filename'];
                $note = $dictionary['note'];
                copy(codecept_data_dir("Billing/{$filename}"), storage_path("app/readonly/{$filename}"));
                $id = $index + 10;
                $ret = system("php {$artisan} {$env} dws-home-help-service-dictionary:import {$id} {$filename} {$dictionary['effectivatedOn']} {$note}");
                $I->assertTrue($ret !== false, 'migration failed');
            }
            self::$isImport = true;
        }
    }
}
