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
 * 介護保険被保険者証マスタテーブルを追加する.
 */
final class CreateLtcsInsCardTable extends Migration
{
    private $ltcsInsCard = 'ltcs_ins_card';
    private $ltcsInsCardAttr = 'ltcs_ins_card_attr';
    private $ltcsInsCardMaxBenefitQuota = 'ltcs_ins_card_max_benefit_quota';
    private $ltcsInsCardServiceType = 'ltcs_ins_card_service_type';
    private $sortOrder = 'sort_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->ltcsInsCardServiceType, '介護保険被保険者証 サービス種別', $this->serviceTypes());
        Schema::create($this->ltcsInsCard, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('介護保険被保険者証ID');
            $table->references('user', '利用者');
            $table->createdAt();
        });
        Schema::create($this->ltcsInsCardAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('介護保険被保険者証属性ID');
            $table->references($this->ltcsInsCard, '介護保険被保険者証');
            $table->catalogued('ltcs_ins_card_status', '介護保険認定区分', 'status');
            $table->catalogued('ltcs_level', '要介護度・要介護状態区分等');
            $table->string('ins_number', 10)->comment('被保険者証番号');
            $table->string('insurer_number', 6)->comment('保険者番号');
            $table->string('insurer_name', 100)->comment('保険者名');
            $table->integer('max_benefit')->comment('区分支給限度基準額');
            $table->integer('copay_rate')->comment('利用者負担割合（原則）');
            $table->date('effectivated_on')->comment('適用日');
            $table->date('issued_on')->comment('交付日');
            $table->date('certificated_on')->comment('認定日');
            $table->date('activated_on')->comment('認定の有効期間（開始）');
            $table->date('deactivated_on')->comment('認定の有効期間（終了）');
            $table->date('copay_activated_on')->comment('利用者負担適用期間（開始）');
            $table->date('copay_deactivated_on')->comment('利用者負担適用期間（終了）');
            $table->attr($this->ltcsInsCard);
        });
        Schema::createAttrIntermediate($this->ltcsInsCard, '介護保険被保険者証');
        Schema::createAttrTriggers($this->ltcsInsCard);
        Schema::create($this->ltcsInsCardMaxBenefitQuota, function (Blueprint $table): void {
            // COLUMNS
            $table->references($this->ltcsInsCardAttr, '介護保険被保険者証属性')->onDelete('cascade');
            // 外部キー制約名が長すぎてMYSQLのエラーになるので catalogued を使用しないで外部キー制約名を指定する
            $table->integer($this->ltcsInsCardServiceType)->unsigned()->comment('サービス内容');
            $table->foreign($this->ltcsInsCardServiceType, 'ltcs_card_service_type_foreign')
                ->references('id')
                ->on($this->ltcsInsCardServiceType);
            $table->integer('max_benefit_quota')->comment('種類支給限度基準額');
            $table->sortOrder();
            // KEYS
            $table->primary(["{$this->ltcsInsCardAttr}_id", $this->sortOrder], "{$this->ltcsInsCardMaxBenefitQuota}_{$this->sortOrder}_primary");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->ltcsInsCardMaxBenefitQuota);
        Schema::dropAttrTriggers($this->ltcsInsCard);
        Schema::dropAttrIntermediate($this->ltcsInsCard);
        Schema::dropIfExists($this->ltcsInsCardAttr);
        Schema::dropIfExists($this->ltcsInsCard);
        Schema::dropIfExists($this->ltcsInsCardServiceType);
    }

    /**
     * 介護保険被保険者証 サービス種別の定義一覧.
     *
     * @return array
     */
    public function serviceTypes(): array
    {
        return [
            [1, 'serviceType1'],
            [2, 'serviceType2'],
            [3, 'serviceType3'],
        ];
    }
}
