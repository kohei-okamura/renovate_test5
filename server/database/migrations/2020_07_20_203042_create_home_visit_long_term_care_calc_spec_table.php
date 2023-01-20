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
 * 事業所算定情報（介保・訪問介護）テーブルを追加する.
 */
class CreateHomeVisitLongTermCareCalcSpecTable extends Migration
{
    private string $homeVisitLongTermCareCalcSpec = 'home_visit_long_term_care_calc_spec';
    private string $homeVisitLongTermCareCalcSpecAttr = 'home_visit_long_term_care_calc_spec_attr';
    private string $homeVisitLongTermCareSpecifiedOfficeAddition = 'home_visit_long_term_care_specified_office_addition';
    private string $ltcsTreatmentImprovementAddition = 'ltcs_treatment_improvement_addition';
    private string $ltcsSpecifiedTreatmentImprovementAddition = 'ltcs_specified_treatment_improvement_addition';
    private string $ltcsOfficeLocationAddition = 'ltcs_office_location_addition';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->homeVisitLongTermCareSpecifiedOfficeAddition, '特定事業所加算区分（介保・訪問介護）', $this->specifiedOfficeAddition());
        Schema::createCatalogue($this->ltcsTreatmentImprovementAddition, '介護職員処遇改善加算（介保）', $this->treatmentImprovement());
        Schema::createCatalogue($this->ltcsSpecifiedTreatmentImprovementAddition, '介護職員等特定処遇改善加算（介保）', $this->specifiedTreatmentImprovement());
        Schema::createCatalogue($this->ltcsOfficeLocationAddition, '地域加算（介保）', $this->officeLocation());
        Schema::create($this->homeVisitLongTermCareCalcSpec, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所算定情報（介保・訪問介護）ID');
            $table->references('office', '事業所');
            $table->createdAt();
        });

        Schema::create($this->homeVisitLongTermCareCalcSpecAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所算定情報（介保・訪問介護）属性ID');
            $table->references($this->homeVisitLongTermCareCalcSpec, '事業所算定情報（介保・訪問介護）');
            $table->date('period_start')->comment('適用期間開始');
            $table->date('period_end')->comment('適用期間終了');
            $table->catalogued($this->homeVisitLongTermCareSpecifiedOfficeAddition, '特定事業所加算', 'specified_office_addition');
            $table->catalogued($this->ltcsTreatmentImprovementAddition, '福祉・介護職員処遇改善加算（介保）', 'treatment_improvement_addition');
            $table->catalogued($this->ltcsSpecifiedTreatmentImprovementAddition, '福祉・介護職員等特定処遇改善加算（介保）', 'specified_treatment_improvement_addition');
            $table->catalogued($this->ltcsOfficeLocationAddition, '地域加算（介保）', 'office_location_addition');
            $table->attr($this->homeVisitLongTermCareCalcSpec);
        });
        Schema::createAttrIntermediate($this->homeVisitLongTermCareCalcSpec, '事業所算定情報（介保・訪問介護）');
        Schema::createAttrTriggers($this->homeVisitLongTermCareCalcSpec);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->homeVisitLongTermCareCalcSpec);
        Schema::dropAttrIntermediate($this->homeVisitLongTermCareCalcSpec);
        Schema::dropIfExists($this->homeVisitLongTermCareCalcSpecAttr);
        Schema::dropIfExists($this->homeVisitLongTermCareCalcSpec);
        Schema::dropIfExists($this->homeVisitLongTermCareSpecifiedOfficeAddition);
        Schema::dropIfExists($this->ltcsSpecifiedTreatmentImprovementAddition);
        Schema::dropIfExists($this->ltcsTreatmentImprovementAddition);
        Schema::dropIfExists($this->ltcsOfficeLocationAddition);
    }

    /**
     * 特定事業所加算区分（介保・訪問介護）の定義一覧.
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

    /**
     * 介護職員処遇改善加算（介保）の定義一覧.
     *
     * @return array
     */
    private function treatmentImprovement(): array
    {
        return [
            [0, 'なし'],
            [1, '処遇改善加算（Ⅰ）'],
            [2, '処遇改善加算（Ⅱ）'],
            [3, '処遇改善加算（Ⅲ）'],
            [4, '処遇改善加算（Ⅳ）'],
            [5, '処遇改善加算（Ⅴ）'],
        ];
    }

    /**
     * 介護職員等特定処遇改善加算（介保）の定義一覧.
     *
     * @return array
     */
    private function specifiedTreatmentImprovement(): array
    {
        return [
            [0, 'なし'],
            [1, '特定処遇改善加算（Ⅰ）'],
            [2, '特定処遇改善加算（Ⅱ）'],
        ];
    }

    /**
     * 地域加算（介保）の定義一覧.
     *
     * @return array
     */
    private function officeLocation(): array
    {
        return [
            [0, 'なし'],
            [1, '特別地域加算'],
            [2, '中山間地域等における小規模事業所加算'],
        ];
    }
}
