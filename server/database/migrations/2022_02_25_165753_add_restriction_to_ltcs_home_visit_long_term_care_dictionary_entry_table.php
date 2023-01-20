<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリーテーブルに下記の変更を加える
 *
 * - 辞書テーブルとの間に外部キー制約を追加し、 辞書テーブルの行削除時に紐づく行が削除されるようにする (DELETE CASCADE)
 * - 辞書ID × サービスコード に UNIQUE 制約を追加して、同じ辞書に同一サービスコードを複数登録できないようにする
 */
class AddRestrictionToLtcsHomeVisitLongTermCareDictionaryEntryTable extends Migration
{
    private const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY = 'ltcs_home_visit_long_term_care_dictionary';
    private const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY = 'ltcs_home_visit_long_term_care_dictionary_entry';
    private const DICTIONARY_ID = 'dictionary_id';
    // 制約名が長すぎてMYSQLのエラーになるので短い制約名を使用する
    private const DICTIONARY_ID_UNIQUE_KEY_IDENTIFIER = 'ltcs_h_v_l_t_c_dict_entry_dictionary_id_service_code_unique';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY, function (Blueprint $table) {
            $fkName = $table->buildForeignKeyName(self::DICTIONARY_ID);
            if ($table->hasForeignKey($fkName)) {
                $table->dropForeign($fkName);
            }
            $table->foreign(self::DICTIONARY_ID, $fkName)
                ->references('id')
                ->on(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY)
                ->cascadeOnDelete();
            $table->unique([self::DICTIONARY_ID, 'service_code'], self::DICTIONARY_ID_UNIQUE_KEY_IDENTIFIER);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY, function (Blueprint $table) {
            $table->dropUnique(self::DICTIONARY_ID_UNIQUE_KEY_IDENTIFIER);
            $table->dropForeign($table->buildForeignKeyName(self::DICTIONARY_ID));
        });
    }
}
