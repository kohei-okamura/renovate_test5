<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 単位数表（総合事業）関連の権限を追加する
 */
final class AddComprehensiveServiceScoreSheetPermissions extends Migration
{
    private const PERMISSION = 'permission';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::seedStringCatalogue(self::PERMISSION, $this->permissions());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::PERMISSION, $this->permissions());
    }

    /**
     * 追加する権限の定義一覧.
     *
     * @return array
     */
    private function permissions(): array
    {
        return [
            ['comprehensive-service-score-sheet/list', '一覧参照'],
            ['comprehensive-service-score-sheet/create', '登録'],
            ['comprehensive-service-score-sheet/update', '編集'],
        ];
    }
}
