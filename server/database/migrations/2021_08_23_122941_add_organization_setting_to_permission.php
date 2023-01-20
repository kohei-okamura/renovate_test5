<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 権限を追加する.
 */
class AddOrganizationSettingToPermission extends Migration
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
    public function down()
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
            ['organization-settings/view', '事業者別設定::詳細参照'],
        ];
    }
}
