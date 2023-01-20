<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 資格マスタテーブルを追加する.
 */
final class CreateCertificationTable extends Migration
{
    private $certification = 'certification';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->certification, '資格', $this->certifications());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->certification);
    }

    /**
     * 資格マスタの定義一覧.
     *
     * @return array
     */
    private function certifications(): array
    {
        return [
            [1, '喀痰吸引研修'],
            [2, '重度訪問介護従業者'],
            [3, '初任者研修'],
            [4, '実務者研修'],
            [5, '介護福祉士'],
            [6, 'ケアマネージャー'],
            [7, '准看護師'],
            [8, '正看護師'],
            [9, '理学療法士'],
            [10, '作業療法士'],
            [11, '普通自動車免許'],
            [12, '社会福祉主事任用資格'],
            [13, '福祉用具専門相談員'],
            [14, '言語聴覚士'],
            [15, 'あん摩マッサージ指圧師'],
            [16, 'はり師'],
            [17, 'きゅう師'],
        ];
    }
}
