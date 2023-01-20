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
 * 事業所算定情報（障害・重度訪問介護）テーブルを追加する.
 */
class CreateVisitingCareForPwsdCalcSpecTable extends Migration
{
    private string $visitingCareForPwsdCalcSpec = 'visiting_care_for_pwsd_calc_spec';
    private string $visitingCareForPwsdCalcSpecAttr = 'visiting_care_for_pwsd_calc_spec_attr';
    private string $visitingCareForPwsdSpecifiedOfficeAddition = 'visiting_care_for_pwsd_specified_office_addition';
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
        Schema::createCatalogue($this->visitingCareForPwsdSpecifiedOfficeAddition, '特定事業所加算区分（障害・重度訪問介護）', $this->specifiedOfficeAddtion());
        Schema::create($this->visitingCareForPwsdCalcSpec, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所算定情報（障害・重度訪問介護）ID');
            $table->references('office', '事業所');
            $table->createdAt();
        });
        Schema::create($this->visitingCareForPwsdCalcSpecAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所算定情報（障害・重度訪問介護）属性ID');
            $table->references($this->visitingCareForPwsdCalcSpec, '事業所算定情報（障害・重度訪問介護）');
            $table->date('period_start')->comment('適用期間開始');
            $table->date('period_end')->comment('適用期間終了');
            $table->catalogued($this->visitingCareForPwsdSpecifiedOfficeAddition, '特定事業所加算', 'specified_office_addition');
            $table->catalogued($this->dwsTreatmentImprovementAddition, '福祉・介護職員処遇改善加算（障害）', 'treatment_improvement_addition');
            $table->catalogued($this->dwsSpecifiedTreatmentImprovementAddition, '福祉・介護職員等特定処遇改善加算（障害）', 'specified_treatment_improvement_addition');
            $table->attr($this->visitingCareForPwsdCalcSpec);
        });
        Schema::createAttrIntermediate($this->visitingCareForPwsdCalcSpec, '事業所算定情報（障害・重度訪問介護）');
        Schema::createAttrTriggers($this->visitingCareForPwsdCalcSpec);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->visitingCareForPwsdCalcSpec);
        Schema::dropAttrIntermediate($this->visitingCareForPwsdCalcSpec);
        Schema::dropIfExists($this->visitingCareForPwsdCalcSpecAttr);
        Schema::dropIfExists($this->visitingCareForPwsdCalcSpec);
        Schema::dropIfExists($this->visitingCareForPwsdSpecifiedOfficeAddition);
    }

    /**
     * 特定事業所加算区分（障害・重度訪問介護）の定義一覧.
     *
     * @return array
     */
    private function specifiedOfficeAddtion(): array
    {
        return [
            [0, 'なし'],
            [1, '特定事業所加算Ⅰ'],
            [2, '特定事業所加算Ⅱ'],
            [3, '特定事業所加算Ⅲ'],
        ];
    }
}
