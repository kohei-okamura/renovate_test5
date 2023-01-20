<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書テーブルを追加する.
 */
final class UpdateServiceOptionDefinitionForLtcs extends Migration
{
    private const SERVICE_OPTION = 'service_option';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::unseedCatalogue(self::SERVICE_OPTION, $this->serviceOptionsForDelete());
        Schema::seedCatalogue(self::SERVICE_OPTION, $this->serviceOptionsForInsert());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::SERVICE_OPTION, $this->serviceOptionsForInsert());
        Schema::seedCatalogue(self::SERVICE_OPTION, $this->serviceOptionsForDelete());
    }

    /**
     * 追加するサービスオプションの一覧.
     *
     * @return array
     */
    private function serviceOptionsForInsert(): array
    {
        return [
            [401101, '生活機能向上連携加算Ⅰ'],
            [401102, '生活機能向上連携加算Ⅱ'],
        ];
    }

    /**
     * 削除するサービスオプションの一覧.
     *
     * @return array
     */
    private function serviceOptionsForDelete(): array
    {
        return [
            [200001, '2人目'],
        ];
    }
}
