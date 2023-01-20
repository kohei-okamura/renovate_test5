<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use ScalikePHP\Seq;

/**
 * 権限を追加する.
 */
class AddUserBillingPermission extends Migration
{
    private const PERMISSION = 'permission';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // name カラムの「表示」を「参照」に統一
        Seq::from(...$this->newPermissions())
            ->each(function (array $permission): void {
                DB::table(self::PERMISSION)->where('id', $permission[0])->update(['name' => $permission[1]]);
            });

        Schema::seedStringCatalogue(self::PERMISSION, $this->userBillingPermissions());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::PERMISSION, $this->userBillingPermissions());
        Seq::from(...$this->oldPermissions())
            ->each(function (array $permission): void {
                DB::table(self::PERMISSION)->where('id', $permission[0])->update(['name' => $permission[1]]);
            });
    }

    /**
     * 利用者請求の権限の定義一覧.
     *
     * @return array
     */
    private function userBillingPermissions()
    {
        return [
            ['user-billings/create', '登録'],
            ['user-billings/list', '一覧参照'],
            ['user-billings/update', '編集'],
            ['user-billings/view', '詳細参照'],
        ];
    }

    /**
     * 変更後の権限の定義一覧.
     *
     * @return array
     */
    private function newPermissions(): array
    {
        return [
            ['attendances/list', '一覧参照'],
            ['attendances/view', '詳細参照'],
            ['billings/list', '一覧参照'],
            ['billings/view', '詳細参照'],
            ['office-groups/list', '一覧参照'],
            ['office-groups/view', '詳細参照'],
            ['offices/list', '一覧参照'],
            ['offices/view', '詳細参照'],
            ['own-expense-programs/list', '一覧参照'],
            ['own-expense-programs/view', '詳細参照'],
            ['roles/list', '一覧参照'],
            ['roles/view', '詳細参照'],
            ['shifts/list', '一覧参照'],
            ['shifts/view', '詳細参照'],
            ['staffs/list', '一覧参照'],
            ['staffs/view', '詳細参照'],
            ['users/list', '一覧参照'],
            ['users/view', '詳細参照'],
        ];
    }

    /**
     * 変更前の権限の定義一覧.
     *
     * @return array
     */
    private function oldPermissions(): array
    {
        return [
            ['attendances/list', '一覧表示'],
            ['attendances/view', '詳細表示'],
            ['billings/list', '一覧表示'],
            ['billings/view', '詳細表示'],
            ['office-groups/list', '一覧表示'],
            ['office-groups/view', '詳細表示'],
            ['offices/list', '一覧表示'],
            ['offices/view', '詳細表示'],
            ['own-expense-programs/list', '一覧表示'],
            ['own-expense-programs/view', '詳細表示'],
            ['roles/list', '一覧表示'],
            ['roles/view', '詳細表示'],
            ['shifts/list', '一覧表示'],
            ['shifts/view', '詳細表示'],
            ['staffs/list', '一覧表示'],
            ['staffs/view', '詳細表示'],
            ['users/list', '一覧表示'],
            ['users/view', '詳細表示'],
        ];
    }
}
