<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 障害福祉サービス・サービスコード区分テーブルを追加する.
 */
class CreateDwsServiceCodeDictionaryTable extends Migration
{
    private string $dwsServiceCodeCategory = 'dws_service_code_category';
    private string $timeframe = 'timeframe';
    private string $dwsHomeHelpServiceProviderType = 'dws_home_help_service_provider_type';
    private string $dwsHomeHelpServiceBuildingType = 'dws_home_help_service_building_type';
    private string $dwsVisitingCareForPwsdDictionary = 'dws_visiting_care_for_pwsd_dictionary';
    private string $dwsVisitingCareForPwsdDictionaryEntry = 'dws_visiting_care_for_pwsd_dictionary_entry';
    private string $dwsHomeHelpServiceDictionary = 'dws_home_help_service_dictionary';
    private string $dwsHomeHelpServiceDictionaryEntry = 'dws_home_help_service_dictionary_entry';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->dwsServiceCodeCategory, '障害福祉サービス・サービスコード区分', $this->dwsServiceCodeCategory());
        Schema::createCatalogue($this->timeframe, '時間帯', $this->timeframe());
        Schema::createCatalogue(
            $this->dwsHomeHelpServiceProviderType,
            '障害居宅介護提供者区分',
            $this->dwsHomeHelpServiceProviderType()
        );
        Schema::createCatalogue(
            $this->dwsHomeHelpServiceBuildingType,
            '障害居宅介護建物区分',
            $this->dwsHomeHelpServiceBuildingType()
        );
        Schema::create($this->dwsVisitingCareForPwsdDictionary, function (Blueprint $table): void {
            // COLUMNS
            $table->bigInteger('id')->unsigned()->unique()->comment('障害福祉サービス：重度訪問介護：サービスコード辞書ID');
            $table->date('effectivated_on')->comment('適用開始日');
            $table->string('name', '100')->comment('名前');
            $table->integer('version')->comment('バージョン');
            $table->createdAt();
            $table->updatedAt();
        });

        Schema::create($this->dwsVisitingCareForPwsdDictionaryEntry, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('サービスコード辞書エントリ（障害：重度訪問介護）ID');
            $table
                ->references(
                    $this->dwsVisitingCareForPwsdDictionary,
                    '障害福祉サービス：重度訪問介護：サービスコード辞書'
                )
                ->onDelete('cascade');
            $table->serviceCode();
            $table->string('name', 100)->comment('名称');
            $table->catalogued($this->dwsServiceCodeCategory, 'サービスコード区分', 'category');
            $table->boolean('is_secondary')->comment('2人（2人目の重度訪問介護従業者による場合）');
            $table->boolean('is_coaching')->comment('同行（熟練従業者が同行して支援を行う場合）');
            $table->boolean('is_hospitalized')->comment('入院（病院等に入院又は入所中に利用した場合）');
            $table->boolean('is_long_hospitalized')->comment('90日（90日以上利用減算）');
            $table->integer('score')->comment('単位数');
            $table->catalogued($this->timeframe, '時間帯');
            $table->integer('duration_start')->comment('時間数 開始');
            $table->integer('duration_end')->comment('時間数 終了');
            $table->integer('unit')->comment('単位');
            $table->createdAt();
            $table->updatedAt();
        });

        Schema::create($this->dwsHomeHelpServiceDictionary, function (Blueprint $table): void {
            // COLUMNS
            $table->bigInteger('id')->unsigned()->unique()->comment('サービスコード辞書エントリ（障害：居宅介護）ID');
            $table->date('effectivated_on')->comment('適用開始日');
            $table->string('name', '100')->comment('名前');
            $table->integer('version')->comment('バージョン');
            $table->createdAt();
            $table->updatedAt();
        });

        Schema::create($this->dwsHomeHelpServiceDictionaryEntry, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('サービスコード辞書エントリ（障害：居宅介護）ID');
            $table->references($this->dwsHomeHelpServiceDictionary, '障害福祉サービス：居宅介護：サービスコード辞書')->onDelete('cascade');
            $table->serviceCode();
            $table->string('name', 100)->comment('名称');
            $table->catalogued($this->dwsServiceCodeCategory, 'サービスコード区分', 'category');
            $table->boolean('is_extra')->comment('増分');
            $table->boolean('is_secondary')->comment('2人（2人目の居宅介護従業者による場合）');
            $table->catalogued($this->dwsHomeHelpServiceProviderType, '提供者区分', 'provider_type');
            $table->boolean('is_planned_by_novice')->comment('初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合）');
            $table->catalogued($this->dwsHomeHelpServiceBuildingType, '障害居宅介護建物区分', 'building_type');
            $table->integer('score')->comment('単位数');
            $table->integer('daytime_duration_start')->comment('時間数（日中） 開始');
            $table->integer('daytime_duration_end')->comment('時間数（日中） 終了');
            $table->integer('morning_duration_start')->comment('時間数（早朝） 開始');
            $table->integer('morning_duration_end')->comment('時間数（早朝） 終了');
            $table->integer('night_duration_start')->comment('時間数（夜間） 開始');
            $table->integer('night_duration_end')->comment('時間数（夜間） 終了');
            $table->integer('midnight_duration1_start')->comment('時間数（深夜1） 開始');
            $table->integer('midnight_duration1_end')->comment('時間数（深夜1） 終了');
            $table->integer('midnight_duration2_start')->comment('時間数（深夜2） 開始');
            $table->integer('midnight_duration2_end')->comment('時間数（深夜2） 終了');
            $table->createdAt();
            $table->updatedAt();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->dwsHomeHelpServiceDictionaryEntry);
        Schema::dropIfExists($this->dwsHomeHelpServiceDictionary);
        Schema::dropIfExists($this->dwsVisitingCareForPwsdDictionaryEntry);
        Schema::dropIfExists($this->dwsVisitingCareForPwsdDictionary);
        Schema::dropIfExists($this->dwsHomeHelpServiceBuildingType);
        Schema::dropIfExists($this->dwsHomeHelpServiceProviderType);
        Schema::dropIfExists($this->timeframe);
        Schema::dropIfExists($this->dwsServiceCodeCategory);
    }

    /**
     * 障害福祉サービス・サービスコード区分の定義一覧.
     *
     * @return array
     */
    private function dwsServiceCodeCategory(): array
    {
        return [
            [111000, '居宅：身体'],
            [112000, '居宅：家事'],
            [113000, '居宅：通院・身体あり'],
            [114000, '居宅：通院・身体なし'],
            [115000, '居宅：乗降介助'],
            [121000, '重訪Ⅰ（重度障害者等の場合）'],
            [122000, '重訪Ⅱ（障害支援区分6に該当する者の場合）'],
            [123000, '重訪Ⅲ'],
            [120901, '重訪（移動加算）'],
            [990101, '特定事業所加算Ⅰ'],
            [990102, '特定事業所加算Ⅱ'],
            [990103, '特定事業所加算Ⅲ'],
            [990104, '特定事業所加算Ⅳ'],
            [990201, '特別地域加算'],
            [990301, '緊急時対応加算'],
            [990401, '喀痰吸引等支援体制加算'],
            [990501, '初回加算'],
            [990601, '利用者負担上限額管理加算'],
            [990701, '福祉専門職員等連携加算'],
            [990702, '行動障害支援連携加算'],
            [990801, '福祉・介護職員処遇改善加算Ⅰ'],
            [990802, '福祉・介護職員処遇改善加算Ⅱ'],
            [990803, '福祉・介護職員処遇改善加算Ⅲ'],
            [990804, '福祉・介護職員処遇改善加算Ⅳ'],
            [990805, '福祉・介護職員処遇改善加算Ⅴ'],
            [990901, '福祉・介護職員処遇改善特別加算'],
            [991001, '福祉・介護職員等特定処遇改善加算Ⅰ'],
            [991002, '福祉・介護職員等特定処遇改善加算Ⅱ'],
        ];
    }

    /**
     * 時間帯の定義一覧.
     *
     * @return array
     */
    private function timeframe(): array
    {
        return [
            [1, '日中'],
            [2, '早朝'],
            [3, '夜間'],
            [4, '深夜'],
            [9, '未定義'],
        ];
    }

    /**
     * 障害居宅介護提供者区分の定義一覧.
     *
     * @return array
     */
    private function dwsHomeHelpServiceProviderType(): array
    {
        return [
            [0, '下記に該当しない'],
            [1, '基（基礎研修課程修了者等により行われる場合）'],
            [2, '重研（重度訪問介護研修修了者による場合）'],
        ];
    }

    /**
     * 障害居宅介護建物区分の定義一覧.
     *
     * @return array
     */
    private function dwsHomeHelpServiceBuildingType(): array
    {
        return [
            [0, '下記に該当しない'],
            [1, '事業所と同一建物の利用者又はこれ以外の同一建物の利用者20人以上にサービスを行う場合'],
            [2, '事業所と同一建物の利用者50人以上にサービスを行う場合'],
        ];
    }
}
