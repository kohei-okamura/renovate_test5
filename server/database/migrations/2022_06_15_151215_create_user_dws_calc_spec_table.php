<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 障害福祉サービス：利用者別算定情報テーブルを追加する.
 */
class CreateUserDwsCalcSpecTable extends Migration
{
    private const DWS_USER_LOCATION_ADDITION = 'dws_user_location_addition';
    private const USER_DWS_CALC_SPEC = 'user_dws_calc_spec';
    private const USER_DWS_CALC_SPEC_ATTR = 'user_dws_calc_spec_attr';
    private const USER = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createCatalogue(
            self::DWS_USER_LOCATION_ADDITION,
            '障害福祉サービス：利用者別地域加算区分',
            $this->locationAdditions()
        );

        Schema::create(self::USER_DWS_CALC_SPEC, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('利用者別算定情報 ID');
            $table->references(self::USER, '利用者');
            $table->createdAt();
        });
        Schema::create(self::USER_DWS_CALC_SPEC_ATTR, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('利用者別算定情報属性 ID');
            $table->references(self::USER_DWS_CALC_SPEC, '利用者別算定情報');
            $table->date('effectivated_on')->comment('適用日');
            $table->catalogued(self::DWS_USER_LOCATION_ADDITION, '地域加算', 'location_addition');
            $table->attr(self::USER_DWS_CALC_SPEC);
        });
        Schema::createAttrIntermediate(self::USER_DWS_CALC_SPEC, '利用者別算定情報');
        Schema::createAttrTriggers(self::USER_DWS_CALC_SPEC);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropAttrTriggers(self::USER_DWS_CALC_SPEC);
        Schema::dropAttrIntermediate(self::USER_DWS_CALC_SPEC);
        Schema::dropIfExists(self::USER_DWS_CALC_SPEC_ATTR);
        Schema::dropIfExists(self::USER_DWS_CALC_SPEC);
        Schema::dropIfExists(self::DWS_USER_LOCATION_ADDITION);
    }

    /**
     * 障害福祉サービス：利用者別地域加算区分の定義一覧.
     *
     * @return array
     */
    private function locationAdditions(): array
    {
        return [
            [0, 'なし'],
            [1, '特別地域加算'],
        ];
    }
}
