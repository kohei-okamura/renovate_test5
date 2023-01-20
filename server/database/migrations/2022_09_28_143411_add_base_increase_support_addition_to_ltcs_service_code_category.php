<?php

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 介護保険サービス：請求：サービスコード区分に訪問介護ベースアップ等支援加算を追加する.
 */
class AddBaseIncreaseSupportAdditionToLtcsServiceCodeCategory extends Migration
{
    private const DWS_SERVICE_CODE_CATEGORY = 'ltcs_service_code_category';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedStringCatalogue(self::DWS_SERVICE_CODE_CATEGORY, $this->categories());
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
     * 追加する指定区分の一覧.
     *
     * @return array
     */
    private function categories(): array
    {
        return [
            ['991401', '訪問介護ベースアップ等支援加算'],
        ];
    }
}
