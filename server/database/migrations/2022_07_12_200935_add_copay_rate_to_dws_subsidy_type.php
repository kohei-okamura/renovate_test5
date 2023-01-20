<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 自治体助成情報：給付方式に定率負担を追加する.
 */
class AddCopayRateToDwsSubsidyType extends Migration
{
    private const DWS_SUBSIDY_TYPE = 'dws_subsidy_type';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::DWS_SUBSIDY_TYPE, $this->types());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::DWS_SUBSIDY_TYPE, $this->types());
    }

    /**
     * 追加する給付方式の一覧.
     *
     * @return array
     */
    private function types(): array
    {
        return [
            [4, '定率負担'],
        ];
    }
}
