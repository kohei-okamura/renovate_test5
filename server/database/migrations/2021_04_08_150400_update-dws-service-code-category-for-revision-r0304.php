<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 障害福祉サービス：請求：サービスコード区分を追加する（令和3年4月改定対応）.
 */
final class UpdateDwsServiceCodeCategoryForRevisionR0304 extends Migration
{
    private const DWS_SERVICE_CODE_CATEGORY = 'dws_service_code_category';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::DWS_SERVICE_CODE_CATEGORY, $this->categories());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::DWS_SERVICE_CODE_CATEGORY, $this->categories());
    }

    /**
     * 追加するサービスコード区分の一覧.
     *
     * @return array
     */
    private function categories(): array
    {
        return [
            [990302, '緊急時対応加算（地域生活拠点）'],
            [991101, '令和3年9月30日までの上乗せ分'],
            [991201, '同一建物減算1'],
            [991202, '同一建物減算2'],
            [991301, '身体拘束廃止未実施減算'],
            [991401, '移動介護緊急時支援加算'],
        ];
    }
}
