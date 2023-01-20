<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Database;

use App\Support\Schema;
use Illuminate\Database\Connectors\SQLiteConnector as IlluminateSQLiteConnector;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use PDO;

/**
 * Sqlite Connector.
 *
 * ref: https://qiita.com/crhg/items/c53e9381f6c976f211c1
 */
class SqliteConnector extends IlluminateSQLiteConnector
{
    public const DWS_HOME_HELP_SERVICE_CHUNK_TABLE = 'dws_home_help_service_chunk';
    public const DWS_VISITING_CARE_FOR_PWSD_CHUNK_TABLE = 'dws_visiting_care_for_pwsd_chunk';
    /** @var array|\PDO[] */
    protected static array $connections = [];

    /**
     * Establish a database connection.
     *
     * 永続的なSQLite Connectionを提供する
     *
     * @param array $config
     * @throws \Exception
     * @return \PDO
     */
    public function connect(array $config): PDO
    {
        $database = Arr::get($config, 'database', '');
        $migrations = Arr::get($config, 'migrations', '');
        if ($database !== ':memory:' || $migrations === '') {
            // @codeCoverageIgnoreStart :memory: のみ対応の前提
            return parent::connect($config);
            // @codeCoverageIgnoreEnd
        }
        if (empty(self::$connections[$migrations])) {
            $options = $this->getOptions($config);
            $connection = $this->createConnection('sqlite::memory:', $config, $options);
            self::$connections[$migrations] = $connection;

            $this->migration();
        }
        return self::$connections[$migrations];
    }

    /**
     * SQLite用のマイグレーション実行.
     *
     * NOTE: 'temporary'へのコネクション固定となっているが、複数必要になった場合に処理を再検討する。
     * NOTE: 固定のテーブルを作成してしまっているが現状問題ないため複数必要になったタイミングで検討する。
     */
    private function migration(): void
    {
        // サービス単位（居宅介護）テーブル作成
        Schema::connection('temporary')->create(self::DWS_HOME_HELP_SERVICE_CHUNK_TABLE, function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('利用者ID');
            $table->integer('category_value')->comment('サービスコード区分値');
            $table->integer('building_type_value')->comment('建物区分値');
            $table->boolean('is_emergency')->comment('緊急時対応');
            $table->boolean('is_planned_by_novice')->comment('初計');
            $table->boolean('is_first')->comment('初回');
            $table->boolean('is_welfare_specialist_cooperation')->comment('福祉専門職員等連携');
            $table->datetime('range_start')->comment('時間範囲 開始');
            $table->datetime('range_end')->comment('時間範囲 終了');
            $table->json('fragments')->comment('要素(配列を json_encode して格納)');
        });
        // サービス単位（重度訪問介護）テーブル作成
        Schema::connection('temporary')->create(self::DWS_VISITING_CARE_FOR_PWSD_CHUNK_TABLE, function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('利用者ID');
            $table->integer('category')->comment('サービスコード区分値');
            $table->boolean('is_emergency')->comment('緊急時対応フラグ');
            $table->boolean('is_first')->comment('初回');
            $table->boolean('is_behavioral_disorder_support_cooperation')->comment('行動障害支援連携加算');
            $table->integer('provided_on')->comment('サービス提供日');
            $table->datetime('range_start')->comment('時間範囲 開始');
            $table->datetime('range_end')->comment('時間範囲 終了');
            $table->json('fragments')->comment('要素(配列を json_encode して格納)');
        });
    }
}
