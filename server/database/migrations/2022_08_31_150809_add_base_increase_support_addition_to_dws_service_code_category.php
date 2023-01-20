<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 障害福祉サービス：請求：サービスコード区分に福祉・介護職員等ベースアップ等支援加算を追加する.
 */
class AddBaseIncreaseSupportAdditionToDwsServiceCodeCategory extends Migration
{
    private const DWS_SERVICE_CODE_CATEGORY = 'dws_service_code_category';

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
            ['991501', '福祉・介護職員等ベースアップ等支援加算'],
        ];
    }
}
