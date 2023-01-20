<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 「福祉・介護職員処遇改善加算」「福祉・介護職員等特定処遇改善加算」カタログテーブルを追加する.
 *
 * Class CreateDwsImprovementAdditionTable
 */
class CreateDwsTreatmentImprovementAdditionTable extends Migration
{
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
        Schema::createCatalogue($this->dwsTreatmentImprovementAddition, '福祉・介護職員処遇改善加算（障害）', $this->treatmentImprovement());
        Schema::createCatalogue($this->dwsSpecifiedTreatmentImprovementAddition, '福祉・介護職員等特定処遇改善加算（障害）', $this->specifiedTreatmentImprovement());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->dwsSpecifiedTreatmentImprovementAddition);
        Schema::dropIfExists($this->dwsTreatmentImprovementAddition);
    }

    /**
     * 福祉・介護職員処遇改善加算（障害）の定義一覧.
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
            [9, '処遇改善特別加算'],
        ];
    }

    /**
     * 福祉・介護職員等特定処遇改善加算（障害）の定義一覧.
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
}
