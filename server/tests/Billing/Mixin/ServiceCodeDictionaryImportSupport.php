<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\Mixin;

use Laravel\Lumen\Application;

/**
 * サービスコード辞書取り込みサポート.
 */
final class ServiceCodeDictionaryImportSupport
{
    /**
     * 障害福祉サービス：居宅 辞書定義.
     *
     * fixer で 移動されてしまうのでこのファイルでしか使わないけどpublic
     * @return array|string[][][]
     */
    public static function homeHelpServiceDictionaries(): array
    {
        return [
            ['filename' => 'dict-dws-11.csv', 'effectivatedOn' => '2021-04-01', 'note' => '令和3年4月版'],
        ];
    }

    /**
     * 取り込み.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public static function import(Application $app)
    {
        self::importHomeHelpServiceDictionaries($app);
    }

    /**
     * 障害福祉サービス：居宅 辞書 取り込み.
     *
     * @param \Laravel\Lumen\Application $app
     */
    private static function importHomeHelpServiceDictionaries(Application $app): void
    {
        $envStr = $app->environment();
        $artisan = $app->basePath('../artisan');
        $env = "--env={$envStr}";
        foreach (self::homeHelpServiceDictionaries() as $index => $dictionary) {
            $filename = $dictionary['filename'];
            $note = $dictionary['note'];
            copy(codecept_data_dir("Billing/{$filename}"), storage_path("app/readonly/{$filename}"));
            $id = $index + 10;
            $ret = system("php {$artisan} {$env} dws-home-help-service-dictionary:import {$id} {$filename} {$dictionary['effectivatedOn']} {$note}");
            assert($ret !== false, 'migration failed');
        }
    }
}
