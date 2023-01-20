<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 介護保険サービス：請求：サービスコード区分を追加する（令和3年4月改定対応）.
 */
final class UpdateLtcsServiceCodeCategoryForRevisionR0304 extends Migration
{
    private const LTCS_SERVICE_CODE_CATEGORY = 'ltcs_service_code_category';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::LTCS_SERVICE_CODE_CATEGORY, $this->categories());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::LTCS_SERVICE_CODE_CATEGORY, $this->categories());
    }

    /**
     * 追加するサービスコード区分の一覧.
     *
     * @return array
     */
    private function categories(): array
    {
        return [
            [991101, '特定事業所加算Ⅰ'],
            [991102, '特定事業所加算Ⅱ'],
            [991103, '特定事業所加算Ⅲ'],
            [991104, '特定事業所加算Ⅳ'],
            [991105, '特定事業所加算Ⅴ'],
            [991201, '認知症専門ケア加算Ⅰ'],
            [991202, '認知症専門ケア加算Ⅱ'],
            [991301, '令和3年9月30日までの上乗せ分'],
        ];
    }
}
