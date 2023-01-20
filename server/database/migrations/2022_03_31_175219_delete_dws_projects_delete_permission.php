<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 権限テーブルから不要な権限「障害福祉サービス計画::削除」を削除する
 * また、上記のために、ロール - 権限 紐付けテーブルから「障害福祉サービス計画::削除」と紐づくデータを削除する
 */
class DeleteDwsProjectsDeletePermission extends Migration
{
    private const PERMISSION = 'permission';
    private const ROLE_PERMISSION = 'role_permission';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // ロールとの紐付きを削除する
        // 当該権限はパーミッショングループに紐づいておらず画面から選択することはできないため、現在存在している紐付きは不要な紐付きとみなす
        // 上記の理由から、当該レコードのロールバック処理は諦める（role_id まで指定すればロールバックは可能だが、その場合、環境依存が発生するため採用しない）
        DB::table(self::ROLE_PERMISSION)->where('permission', 'dws-projects/delete')->delete();
        Schema::unseedCatalogue(self::PERMISSION, $this->permissions());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::seedStringCatalogue(self::PERMISSION, $this->permissions());
    }

    /**
     * 削除する権限の定義一覧.
     *
     * @return array
     */
    private function permissions(): array
    {
        return [
            ['dws-projects/delete', '障害福祉サービス計画::削除'],
        ];
    }
}
