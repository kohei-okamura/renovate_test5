<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 公費情報テーブルを作成する.
 */
class CreateSubsidy extends Migration
{
    private $userDwsSubsidy = 'user_dws_subsidy';
    private $userDwsSubsidyAttr = 'user_dws_subsidy_attr';
    private $dwsSubsidyType = 'dws_subsidy_type';
    private $ltcsSubsidy = 'user_ltcs_subsidy';
    private $ltcsSubsidyAttr = 'user_ltcs_subsidy_attr';
    private $user = 'user';
    private $defrayerCategory = 'defrayer_category';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::createCatalogue(
            $this->dwsSubsidyType,
            '給付方式',
            $this->dwsSubsidyType(),
        );
        Schema::create($this->userDwsSubsidy, function (Blueprint $table) {
            $table->id()->comment('自治体助成情報ID');
            $table->references($this->user, '利用者');
            $table->createdAt();
        });
        Schema::create($this->userDwsSubsidyAttr, function (Blueprint $table) {
            $table->id()->comment('自治体助成情報属性ID');
            $table->references($this->userDwsSubsidy, '自治体助成情報');
            $table->date('period_start')->comment('適用期間（開始）');
            $table->date('period_end')->comment('適用期間（終了）');
            $table->string('city_name', 100)->comment('助成自治体名');
            $table->string('city_code', 6)->charset('binary')->comment('助成自治体番号');
            $table->catalogued($this->dwsSubsidyType, '給付方式');
            $table->unsignedInteger('benefit_rate')->comment('給付率[%]');
            $table->unsignedInteger('benefit_amount')->comment('給付額');
            $table->unsignedInteger('copay')->comment('本人負担額');
            $table->string('note')->comment('備考');
            $table->attr($this->userDwsSubsidy);
        });
        Schema::createAttrIntermediate($this->userDwsSubsidy, '自治体助成情報');
        Schema::createAttrTriggers($this->userDwsSubsidy);

        Schema::createCatalogue(
            $this->defrayerCategory,
            '公費制度種別',
            $this->defrayerCategory()
        );
        Schema::create($this->ltcsSubsidy, function (Blueprint $table) {
            $table->id()->comment('公費情報ID');
            $table->references($this->user, '利用者');
            $table->createdAt();
        });
        Schema::create($this->ltcsSubsidyAttr, function (Blueprint $table) {
            $table->id()->comment('公費情報属性ID');
            $table->references($this->ltcsSubsidy, '公費情報');
            $table->date('period_start')->comment('適用期間（開始）');
            $table->date('period_end')->comment('適用期間（終了）');
            $table->catalogued($this->defrayerCategory, '公費制度種別');
            $table->string('defrayer_number', 20)->comment('負担者番号');
            $table->string('recipient_number', 20)->comment('受給者番号');
            $table->unsignedInteger('benefit_rate')->comment('給付率[%]');
            $table->unsignedInteger('copay')->comment('本人負担額');
            $table->attr($this->ltcsSubsidy);
        });
        Schema::createAttrIntermediate($this->ltcsSubsidy, '公費情報');
        Schema::createAttrTriggers($this->ltcsSubsidy);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropAttrTriggers($this->userDwsSubsidy);
        Schema::dropAttrIntermediate($this->userDwsSubsidy);
        Schema::dropIfExists($this->userDwsSubsidyAttr);
        Schema::dropIfExists($this->dwsSubsidyType);
        Schema::dropIfExists($this->userDwsSubsidy);
        Schema::dropAttrTriggers($this->ltcsSubsidy);
        Schema::dropAttrIntermediate($this->ltcsSubsidy);
        Schema::dropIfExists($this->ltcsSubsidyAttr);
        Schema::dropIfExists($this->defrayerCategory);
        Schema::dropIfExists($this->ltcsSubsidy);
    }

    /**
     * 公費制度種別 の定義一覧
     *
     * @return array
     */
    protected function defrayerCategory(): array
    {
        return [
            [58, '【58】特別対策（全額免除）'],
            [81, '【81】原爆（福祉）'],
            [25, '【25】中国残留法人'],
            [12, '【12】生活保護'],
        ];
    }

    /**
     * 給付方式 の定義一覧.
     *
     * @return array|array[]
     */
    protected function dwsSubsidyType(): array
    {
        return [
            [1, '定率給付'],
            [2, '定額給付'],
            [3, '定額負担'],
        ];
    }
}
