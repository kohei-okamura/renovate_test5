<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書テーブルを追加する.
 */
final class CreateLtcsHomeVisitLongTermCareDictionaryTable extends Migration
{
    // 介護保険サービス：訪問介護：サービスコード辞書
    private const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY = 'ltcs_home_visit_long_term_care_dictionary';

    // 介護保険サービス：訪問介護：サービスコード辞書エントリ
    private const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY = 'ltcs_home_visit_long_term_care_dictionary_entry';

    // 介護保険サービス：サービスコード区分
    private const LTCS_SERVICE_CODE_CATEGORY = 'ltcs_service_code_category';

    // 介護保険サービス：合成識別区分
    private const LTCS_COMPOSITION_TYPE = 'ltcs_composition_type';

    // 介護保険サービス：摘要欄記載要件
    private const LTCS_NOTE_REQUIREMENT = 'ltcs_note_requirement';

    // 介護保険サービス：単位値区分
    private const LTCS_CALC_TYPE = 'ltcs_calc_type';

    // 介護保険サービス：算定単位
    private const LTCS_CALC_CYCLE = 'ltcs_calc_cycle';

    // 介護保険サービス：訪問介護：特定事業所加算区分
    private const HOME_VISIT_LONG_TERM_CARE_SPECIFIED_OFFICE_ADDITION = 'home_visit_long_term_care_specified_office_addition';

    // 時間帯
    private const TIMEFRAME = 'timeframe';

    /**
     * Run the migrations.
     *
     * @return void
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function up(): void
    {
        $this->down();

        Schema::create(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('辞書 ID');
            $table->date('effectivated_on')->comment('適用開始日');
            $table->string('name', 100)->comment('名前');
            $table->integer('version')->comment('バージョン');
            $table->createdAt();
            $table->updatedAt();
        });

        Schema::createCatalogue(
            self::LTCS_SERVICE_CODE_CATEGORY,
            '介護保険サービス：サービスコード区分'
        );
        Schema::seedCatalogue(self::LTCS_SERVICE_CODE_CATEGORY, $this->ltcsServiceCodeCategories());

        Schema::createCatalogue(
            self::LTCS_COMPOSITION_TYPE,
            '介護保険サービス：合成識別区分'
        );
        Schema::seedCatalogue(self::LTCS_COMPOSITION_TYPE, $this->ltcsCompositionTypes());

        Schema::createCatalogue(
            self::LTCS_NOTE_REQUIREMENT,
            '介護保険サービス：摘要欄記載要件'
        );
        Schema::seedCatalogue(self::LTCS_NOTE_REQUIREMENT, $this->ltcsNoteRequirements());

        Schema::createCatalogue(
            self::LTCS_CALC_TYPE,
            '介護保険サービス：単位値区分'
        );
        Schema::seedCatalogue(self::LTCS_CALC_TYPE, $this->ltcsCalcTypes());

        Schema::createCatalogue(
            self::LTCS_CALC_CYCLE,
            '介護保険サービス：算定単位'
        );
        Schema::seedCatalogue(self::LTCS_CALC_CYCLE, $this->ltcsCalcCycles());

        Schema::create(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('辞書エントリ ID');
            $table->foreignId('dictionary_id')
                ->comment('辞書 ID')
                ->constrained(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY)
                ->index($table->buildForeignKeyName('dictionary_id'));
            $table->serviceCode();
            $table->string('name', 100)->comment('名称');
            $table->catalogued(self::LTCS_SERVICE_CODE_CATEGORY, 'サービスコード区分', 'category');
            $table->tinyInteger('headcount')->comment('提供人数');
            $table->catalogued(self::LTCS_COMPOSITION_TYPE, '合成識別区分', 'composition_type');
            $table->catalogued(
                self::HOME_VISIT_LONG_TERM_CARE_SPECIFIED_OFFICE_ADDITION,
                '特定事業所加算',
                'specified_office_addition'
            );
            $table->catalogued(self::LTCS_NOTE_REQUIREMENT, '摘要欄記載要件', 'note_requirement');
            $table->boolean('is_limited')->comment('支給限度額対象');
            $table->boolean('is_bulk_subtraction_target')->comment('同一建物減算対象');
            $table->boolean('is_symbiotic_subtraction_target')->comment('共生型減算対象');
            $table->integer('score_value')->comment('算定単位数：単位値');
            $table->catalogued(self::LTCS_CALC_TYPE, '算定単位数：単位値区分', 'score_calc_type');
            $table->catalogued(self::LTCS_CALC_CYCLE, '算定単位数：算定単位', 'score_calc_cycle');
            $table->boolean('extra_score_is_available')->comment('きざみ単位数：きざみ有無');
            $table->integer('extra_score_base_minutes')->comment('きざみ単位数：きざみ基準時間数');
            $table->integer('extra_score_unit_score')->comment('きざみ単位数：きざみ単位数');
            $table->integer('extra_score_unit_minutes')->comment('きざみ単位数：きざみ時間量');
            $table->integer('extra_score_specified_office_addition_coefficient')->comment('きざみ単位数：特定事業所加算係数');
            $table->integer('extra_score_timeframe_addition_coefficient')->comment('きざみ単位数：時間帯係数');
            $table->catalogued(self::TIMEFRAME, '時間帯');
            $table->integer('total_minutes_start')->comment('合計時間数：最小');
            $table->integer('total_minutes_end')->comment('合計時間数：最大');
            $table->integer('physical_minutes_start')->comment('身体時間数：最小');
            $table->integer('physical_minutes_end')->comment('身体時間数：最大');
            $table->integer('housework_minutes_start')->comment('生活時間数：最小');
            $table->integer('housework_minutes_end')->comment('生活時間数：最大');
            $table->createdAt();
            $table->updatedAt();
            // KEYS
            $table->index(
                [
                    'dictionary_id',
                    'category',
                    'headcount',
                    'timeframe',
                    'specified_office_addition',
                    'total_minutes_start',
                    'total_minutes_end',
                    'physical_minutes_start',
                    'physical_minutes_end',
                    'housework_minutes_start',
                    'housework_minutes_end',
                ],
                'ltcs_service_code_dictionary_entry_find_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY);
        Schema::dropIfExists(self::LTCS_CALC_CYCLE);
        Schema::dropIfExists(self::LTCS_CALC_TYPE);
        Schema::dropIfExists(self::LTCS_NOTE_REQUIREMENT);
        Schema::dropIfExists(self::LTCS_COMPOSITION_TYPE);
        Schema::dropIfExists(self::LTCS_SERVICE_CODE_CATEGORY);
        Schema::dropIfExists(self::LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY);
    }

    /**
     * 介護保険サービス：サービスコード区分の定義一覧.
     *
     * @return array
     */
    private function ltcsServiceCodeCategories(): array
    {
        return [
            [111000, '身体'],
            [112000, '身体＋生活'],
            [113000, '生活'],
            [990101, '緊急時訪問介護加算'],
            [990201, '初回加算'],
            [990301, '生活機能向上連携加算Ⅰ'],
            [990302, '生活機能向上連携加算Ⅱ'],
            [990401, '同一建物減算Ⅰ'],
            [990402, '同一建物減算Ⅱ'],
            [990501, '介護職員処遇改善加算Ⅰ'],
            [990502, '介護職員処遇改善加算Ⅱ'],
            [990503, '介護職員処遇改善加算Ⅲ'],
            [990504, '介護職員処遇改善加算Ⅳ'],
            [990505, '介護職員処遇改善加算Ⅴ'],
            [990601, '介護職員等特定処遇改善加算Ⅰ'],
            [990602, '介護職員等特定処遇改善加算Ⅱ'],
            [990701, '共生型サービス減算（居宅介護1）'],
            [990702, '共生型サービス減算（居宅介護2）'],
            [990711, '共生型サービス減算（重度訪問介護）'],
            [990801, '特別地域訪問介護加算'],
            [990901, '小規模事業所加算（中山間地域等における小規模事業所加算）'],
            [991001, '中山間地域等提供加算（中山間地域等に居住する者へのサービス提供加算）'],
        ];
    }

    /**
     * 介護保険サービス：合成識別区分の定義一覧.
     *
     * @return array
     */
    private function ltcsCompositionTypes(): array
    {
        return [
            [1, '基本サービスコード'],
            [2, '合成サービスコード'],
            [3, '単独加減算サービスコード'],
        ];
    }

    /**
     * 介護保険サービス：摘要欄記載要件の定義一覧.
     *
     * @return array
     */
    private function ltcsNoteRequirements(): array
    {
        return [
            [1, '所要時間'],
            [99, '空白（記載不要）'],
        ];
    }

    /**
     * 介護保険サービス：単位値区分の定義一覧.
     *
     * @return array
     */
    private function ltcsCalcTypes(): array
    {
        return [
            [11, '単位数'],
            [12, 'きざみ基準単位数'],
            [21, '%値'],
            [22, '1/1000値'],
        ];
    }

    /**
     * 介護保険サービス：算定単位の定義一覧.
     *
     * @return array
     */
    private function ltcsCalcCycles(): array
    {
        return [
            [1, '1回につき'],
            [2, '1日につき'],
            [3, '1月につき'],
        ];
    }
}
