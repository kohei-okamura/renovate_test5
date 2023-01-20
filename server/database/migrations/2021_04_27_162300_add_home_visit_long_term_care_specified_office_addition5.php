<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 介護保険サービス：訪問介護：特定事業所加算区分に「特定事業所加算Ⅴ」を追加する.
 */
final class AddHomeVisitLongTermCareSpecifiedOfficeAddition5 extends Migration
{
    private const HOME_VISIT_LONG_TERM_CARE_SPECIFIED_OFFICE_ADDITION = 'home_visit_long_term_care_specified_office_addition';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::HOME_VISIT_LONG_TERM_CARE_SPECIFIED_OFFICE_ADDITION, $this->additions());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::HOME_VISIT_LONG_TERM_CARE_SPECIFIED_OFFICE_ADDITION, $this->additions());
    }

    /**
     * 追加する区分値の一覧.
     *
     * @return array
     */
    private function additions(): array
    {
        return [
            [5, '特定事業所加算Ⅴ'],
            [35, '特定事業所加算Ⅲ＋Ⅴ'],
        ];
    }
}
