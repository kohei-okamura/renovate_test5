<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 障害福祉サービス認定区分マスタテーブルを追加する.
 */
final class CreateDwsCertificationStatusTable extends Migration
{
    private $dwsCertificationStatus = 'dws_certification_status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->dwsCertificationStatus, '障害福祉サービス認定区分', $this->status());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->dwsCertificationStatus);
    }

    protected function status()
    {
        return [
            [1, '申請中'],
            [2, '認定済'],
        ];
    }
}
