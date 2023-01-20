<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

class AddUserLtcsCalcSpecPermission extends Migration
{
    private const PERMISSION = 'permission';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
            ['user-ltcs-calc-specs/create', '介護保険サービス算定情報::登録'],
            ['user-ltcs-calc-specs/update', '介護保険サービス算定情報::編集'],
            ['user-ltcs-calc-specs/list', '介護保険サービス算定情報::一覧参照'],
        ];
    }
}
