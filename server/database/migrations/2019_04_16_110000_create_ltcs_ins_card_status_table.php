<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 介護保険認定区分マスタテーブルを追加する.
 */
final class CreateLtcsInsCardStatusTable extends Migration
{
    private $ltcsInsCardStatus = 'ltcs_ins_card_status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->ltcsInsCardStatus, '介護保険認定区分', $this->ltcsCardStatuses());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->ltcsInsCardStatus);
    }

    /**
     * 介護保険被保険者証の定義一覧.
     *
     * @return array
     */
    protected function ltcsCardStatuses(): array
    {
        return [
            [1, '申請中'],
            [2, '認定済'],
        ];
    }
}
