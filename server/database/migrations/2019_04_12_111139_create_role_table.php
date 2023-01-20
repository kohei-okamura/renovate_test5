<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * ロールマスタテーブルを追加する.
 */
final class CreateRoleTable extends Migration
{
    private const PERMISSION_GROUP = 'permission_group';
    private const PERMISSION = 'permission';
    private const ROLE_SCOPE = 'role_scope';
    private const ROLE = 'role';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();

        Schema::createStringCatalogue(self::PERMISSION, '権限', $this->permissions());

        Schema::create(self::PERMISSION_GROUP, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('権限グループID');
            $table->code(100)->unique()->comment('権限グループコード');
            $table->string('name', 100)->comment('権限グループ名');
            $table->string('display_name', 100)->comment('表示名');
            $table->sortOrder()->unique();
            $table->createdAt();
        });

        Schema::create(self::PERMISSION_GROUP . '_' . self::PERMISSION, function (Blueprint $table): void {
            $table->references(self::PERMISSION_GROUP, '権限グループ')->onDelete('cascade');
            $table->string('permission', 100)->charset('binary')->comment('権限ID');
            $table->foreign('permission')->references('id')->on('permission');
            $table->primary(
                [self::PERMISSION_GROUP . '_id', 'permission'],
                self::PERMISSION_GROUP . '_permission_primary'
            );
        });

        Schema::createCatalogue(self::ROLE_SCOPE, '権限範囲', $this->roleScopes());

        Schema::create(self::ROLE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('ロールID');
            $table->references('organization', '事業者');
            $table->string('name', 100)->comment('ロール名');
            $table->boolean('is_system_admin')->comment('システム管理者フラグ');
            $table->catalogued(self::ROLE_SCOPE, '権限範囲', 'scope');
            $table->sortOrder();
            $table->createdAt();
            $table->updatedAt();
            // KEYS
            $table->unique(['organization_id', 'sort_order'], 'role_sort_order_unique');
        });

        // 権限は他の区分値と異なり「値が文字列である」であるためマクロを用いずにテーブルを作成する
        // 今後値が文字列である区分値が他にも増えるようであればマクロ化を検討する
        Schema::create(self::ROLE . '_' . self::PERMISSION, function (Blueprint $table): void {
            $table->references(self::ROLE, 'ロール')->onDelete('cascade');
            $table->string('permission', 100)->charset('binary')->comment('権限ID');
            $table->foreign('permission')->references('id')->on('permission');
            $table->primary(
                [self::ROLE . '_id', 'permission'],
                self::ROLE . '_permission_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropCatalogueIntermediate(self::ROLE, self::PERMISSION);
        Schema::dropIfExists(self::ROLE);
        Schema::dropIfExists(self::ROLE_SCOPE);
        Schema::dropCatalogueIntermediate(self::PERMISSION_GROUP, self::PERMISSION);
        Schema::dropIfExists(self::PERMISSION_GROUP);
        Schema::dropIfExists(self::PERMISSION);
    }

    /**
     * 権限の定義一覧.
     *
     * @return array
     */
    private function permissions(): array
    {
        return [
            ['users/list', '一覧表示'],
            ['users/view', '詳細表示'],
            ['users/create', '登録'],
            ['users/update', '編集'],
            ['users/delete', '削除'],
            ['users/bank-account/view', '銀行口座::参照'],
            ['users/bank-account/update', '銀行口座::編集'],
            ['dws-contracts/list', '障害福祉サービス契約::一覧参照'],
            ['dws-contracts/view', '障害福祉サービス契約::詳細参照'],
            ['dws-contracts/create', '障害福祉サービス契約::登録'],
            ['dws-contracts/update', '障害福祉サービス契約::編集'],
            ['dws-contracts/delete', '障害福祉サービス契約::削除'],
            ['dws-certifications/list', '障害福祉サービス受給者証::一覧参照'],
            ['dws-certifications/view', '障害福祉サービス受給者証::詳細参照'],
            ['dws-certifications/create', '障害福祉サービス受給者証::登録'],
            ['dws-certifications/update', '障害福祉サービス受給者証::編集'],
            ['dws-certifications/delete', '障害福祉サービス受給者証::削除'],
            ['user-dws-subsidies/list', '障害福祉サービス自治体助成情報::一覧参照'],
            ['user-dws-subsidies/view', '障害福祉サービス自治体助成情報::詳細参照'],
            ['user-dws-subsidies/create', '障害福祉サービス自治体助成情報::登録'],
            ['user-dws-subsidies/update', '障害福祉サービス自治体助成情報::編集'],
            ['dws-projects/list', '障害福祉サービス計画::一覧参照'],
            ['dws-projects/view', '障害福祉サービス計画::詳細参照'],
            ['dws-projects/create', '障害福祉サービス計画::登録'],
            ['dws-projects/update', '障害福祉サービス計画::編集'],
            ['dws-projects/delete', '障害福祉サービス計画::削除'],
            ['ltcs-contracts/list', '介護保険サービス契約::一覧参照'],
            ['ltcs-contracts/view', '介護保険サービス契約::詳細参照'],
            ['ltcs-contracts/create', '介護保険サービス契約::登録'],
            ['ltcs-contracts/update', '介護保険サービス契約::編集'],
            ['ltcs-contracts/delete', '介護保険サービス契約::削除'],
            ['ltcs-ins-cards/list', '介護保険被保険者証::一覧参照'],
            ['ltcs-ins-cards/view', '介護保険被保険者証::詳細参照'],
            ['ltcs-ins-cards/create', '介護保険被保険者証::登録'],
            ['ltcs-ins-cards/update', '介護保険被保険者証::編集'],
            ['ltcs-ins-cards/delete', '介護保険被保険者証::削除'],
            ['user-ltcs-subsidies/list', '介護保険サービス公費情報::一覧参照'],
            ['user-ltcs-subsidies/view', '介護保険サービス公費情報::詳細参照'],
            ['user-ltcs-subsidies/create', '介護保険サービス公費情報::登録'],
            ['user-ltcs-subsidies/update', '介護保険サービス公費情報::編集'],
            ['user-ltcs-subsidies/delete', '介護保険サービス公費情報::削除'],
            ['ltcs-projects/list', '介護保険サービス計画::一覧参照'],
            ['ltcs-projects/view', '介護保険サービス計画::詳細参照'],
            ['ltcs-projects/create', '介護保険サービス計画::登録'],
            ['ltcs-projects/update', '介護保険サービス計画::編集'],
            ['ltcs-projects/delete', '介護保険サービス計画::削除'],
            ['staffs/list', '一覧表示'],
            ['staffs/view', '詳細表示'],
            ['staffs/create', '登録'],
            ['staffs/update', '編集'],
            ['staffs/delete', '削除'],
            ['offices/list', '一覧表示'],
            ['offices/view', '詳細表示'],
            ['offices/create', '登録'],
            ['offices/update', '編集'],
            ['offices/delete', '削除'],
            ['office-groups/list', '一覧表示'],
            ['office-groups/view', '詳細表示'],
            ['office-groups/create', '登録'],
            ['office-groups/update', '編集'],
            ['office-groups/delete', '削除'],
            ['shifts/list', '一覧表示'],
            ['shifts/view', '詳細表示'],
            ['shifts/create', '登録'],
            ['shifts/import', '一括登録'],
            ['shifts/update', '編集'],
            ['shifts/delete', '削除'],
            ['attendances/list', '一覧表示'],
            ['attendances/view', '詳細表示'],
            ['attendances/create', '登録'],
            ['attendances/update', '編集'],
            ['attendances/delete', '削除'],
            ['dws-provision-reports/list', '障害福祉サービス予実::一覧参照'],
            ['dws-provision-reports/update', '障害福祉サービス予実::登録／編集'],
            ['ltcs-provision-reports/list', '介護保険サービス予実::一覧参照'],
            ['ltcs-provision-reports/update', '介護保険サービス予実::登録／編集'],
            ['billings/list', '一覧表示'],
            ['billings/view', '詳細表示'],
            ['billings/create', '登録'],
            ['billings/update', '編集'],
            ['billings/delete', '削除'],
            ['billings/download', 'ダウンロード'],
            ['roles/list', '一覧表示'],
            ['roles/view', '詳細表示'],
            ['roles/create', '登録'],
            ['roles/update', '編集'],
            ['roles/delete', '削除'],
            ['own-expense-programs/list', '一覧表示'],
            ['own-expense-programs/view', '詳細表示'],
            ['own-expense-programs/create', '登録'],
            ['own-expense-programs/update', '編集'],
        ];
    }

    /**
     * 権限範囲の定義一覧.
     *
     * @return array
     */
    private function roleScopes(): array
    {
        return [
            [1, '全体'],
            [2, 'グループ'],
            [3, '事業所'],
            [4, '個人'],
        ];
    }
}
