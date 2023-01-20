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
 * 事業所算定情報（障害・居宅介護）テーブルを追加する.
 */
class CreateHomeHelpServiceCalcSpecTable extends Migration
{
    private string $homeHelpServiceCalcSpec = 'home_help_service_calc_spec';
    private string $homeHelpServiceCalcSpecAttr = 'home_help_service_calc_spec_attr';
    private string $homeHelpServiceSpecifiedOfficeAddition = 'home_help_service_specified_office_addition';
    private string $dwsTreatmentImprovementAddition = 'dws_treatment_improvement_addition';
    private string $dwsSpecifiedTreatmentImprovementAddition = 'dws_specified_treatment_improvement_addition';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->homeHelpServiceSpecifiedOfficeAddition, '特定事業所加算区分（障害・居宅介護）', $this->specifiedOfficeAddition());
        Schema::create($this->homeHelpServiceCalcSpec, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所算定情報（障害・居宅介護）ID');
            $table->references('office', '事業所');
            $table->createdAt();
        });
        Schema::create($this->homeHelpServiceCalcSpecAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所算定情報（障害・居宅介護）属性ID');
            $table->references($this->homeHelpServiceCalcSpec, '事業所算定情報（障害・居宅介護）');
            $table->date('period_start')->comment('適用期間開始');
            $table->date('period_end')->comment('適用期間終了');
            $table->catalogued($this->homeHelpServiceSpecifiedOfficeAddition, '特定事業所加算', 'specified_office_addition');
            $table->catalogued($this->dwsTreatmentImprovementAddition, '福祉・介護職員処遇改善加算（障害）', 'treatment_improvement_addition');
            $table->catalogued($this->dwsSpecifiedTreatmentImprovementAddition, '福祉・介護職員等特定処遇改善加算（障害）', 'specified_treatment_improvement_addition');
            $table->attr($this->homeHelpServiceCalcSpec);
        });
        Schema::createAttrIntermediate($this->homeHelpServiceCalcSpec, '事業所算定情報（障害・居宅介護）');
        Schema::createAttrTriggers($this->homeHelpServiceCalcSpec);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->homeHelpServiceCalcSpec);
        Schema::dropAttrIntermediate($this->homeHelpServiceCalcSpec);
        Schema::dropIfExists($this->homeHelpServiceCalcSpecAttr);
        Schema::dropIfExists($this->homeHelpServiceCalcSpec);
        Schema::dropIfExists($this->homeHelpServiceSpecifiedOfficeAddition);
    }

    /**
     * 特定事業所加算区分（障害・居宅介護）の定義一覧.
     *
     * @return array
     */
    private function specifiedOfficeAddition(): array
    {
        return [
            [0, 'なし'],
            [1, '特定事業所加算Ⅰ'],
            [2, '特定事業所加算Ⅱ'],
            [3, '特定事業所加算Ⅲ'],
            [4, '特定事業所加算Ⅳ'],
        ];
    }
}
