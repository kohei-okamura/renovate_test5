<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use ScalikePHP\Seq;

class ChangeOfficePermission extends Migration
{
    private const PERMISSION = 'permission';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // name カラムの「事業所」を「事業所（自社）」へ変更
        Seq::from(...$this->newInternalOfficePermissions())->each(function (array $permission): void {
            DB::table(self::PERMISSION)->where('id', $permission[0])->update(['name' => $permission[1]]);
        });

        // 「事業所（他社）」権限を追加
        Schema::seedStringCatalogue(self::PERMISSION, $this->externalOfficePermissions());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::unseedCatalogue(self::PERMISSION, $this->externalOfficePermissions());

        Seq::from(...$this->oldInternalOfficePermissions())->each(function (array $permission): void {
            DB::table(self::PERMISSION)->where('id', $permission[0])->update(['name' => $permission[1]]);
        });
    }

    /**
     * 変更後の自社事業所の権限の定義一覧.
     *
     * @return array
     */
    private function newInternalOfficePermissions(): array
    {
        return [
            ['offices/create', '登録（自社）'],
            ['offices/list', '一覧参照（自社）'],
            ['offices/update', '編集（自社）'],
            ['offices/view', '詳細参照（自社）'],
            ['offices/delete', '削除（自社）'],
        ];
    }

    /**
     * 変更前の自社事業所の権限の定義一覧.
     *
     * @return array
     */
    private function oldInternalOfficePermissions(): array
    {
        return [
            ['offices/create', '登録'],
            ['offices/list', '一覧参照'],
            ['offices/update', '編集'],
            ['offices/view', '詳細参照'],
            ['offices/delete', '削除'],
        ];
    }

    /**
     * 他社事業所の権限の定義一覧.
     *
     * @return array
     */
    private function externalOfficePermissions(): array
    {
        return [
            ['external-offices/create', '登録（他社）'],
            ['external-offices/list', '一覧参照（他社）'],
            ['external-offices/update', '編集（他社）'],
            ['external-offices/view', '詳細参照（他社）'],
            ['external-offices/delete', '削除（他社）'],
        ];
    }
}
