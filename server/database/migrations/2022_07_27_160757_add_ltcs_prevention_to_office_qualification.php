<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 事業所：指定区分に介護予防支援（介護保険サービス）を追加する.
 * また、論理名を以下のように修正する.
 * 「訪問型サービス（介護保険サービス・総合事業）」→「総合事業・訪問型サービス（介護保険サービス）」
 * 「移動支援（障害福祉サービス・地域生活支援）」→「地域生活支援事業・移動支援（障害福祉サービス）」
 */
class AddLtcsPreventionToOfficeQualification extends Migration
{
    private const OFFICE_QUALIFICATION = 'office_qualification';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::OFFICE_QUALIFICATION, $this->types());
        DB::table(self::OFFICE_QUALIFICATION)
            ->where('id', '1072')
            ->update(['name' => '地域生活支援事業・移動支援（障害福祉サービス）']);
        DB::table(self::OFFICE_QUALIFICATION)
            ->where('id', '20A0')
            ->update(['name' => '総合事業・訪問型サービス（介護保険サービス）']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::OFFICE_QUALIFICATION, $this->types());
        DB::table(self::OFFICE_QUALIFICATION)
            ->where('id', '1072')
            ->update(['name' => '移動支援（障害福祉サービス・地域生活支援）']);
        DB::table(self::OFFICE_QUALIFICATION)
            ->where('id', '20A0')
            ->update(['name' => '訪問型サービス（介護保険サービス・総合事業）']);
    }

    /**
     * 追加する指定区分の一覧.
     *
     * @return array
     */
    private function types(): array
    {
        return [
            ['20A4', '介護予防支援（介護保険サービス）'],
        ];
    }
}
