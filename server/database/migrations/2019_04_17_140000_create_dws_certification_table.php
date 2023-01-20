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
 * 障害福祉サービス受給者証テーブルを追加する.
 */
final class CreateDwsCertificationTable extends Migration
{
    private $copayCoordinationType = 'copay_coordination_type';
    private $dwsCertification = 'dws_certification';
    private $dwsCertificationAgreement = 'dws_certification_agreement';
    private $dwsCertificationAgreementType = 'dws_certification_agreement_type';
    private $dwsCertificationAttr = 'dws_certification_attr';
    private $dwsCertificationGrant = 'dws_certification_grant';
    private $dwsCertificationServiceType = 'dws_certification_service_type';
    private $dwsCertificationStatus = 'dws_certification_status';
    private $dwsLevel = 'dws_level';
    private $dwsType = 'dws_type';
    private $sortOrder = 'sort_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue(
            $this->dwsCertificationAgreementType,
            '障害福祉サービス受給者証 サービス内容',
            $this->dwsCertificationAgreementType()
        );
        Schema::createCatalogue($this->dwsLevel, '障害支援区分', $this->dwsLevel());
        Schema::createCatalogue($this->dwsType, '障害種別', $this->dwsType());
        Schema::createCatalogue(
            $this->dwsCertificationServiceType,
            '障害福祉サービス受給者証 サービス種別',
            $this->dwsCertificationServiceType()
        );
        Schema::createCatalogue($this->copayCoordinationType, '上限管理区分', $this->copayCoordinationType());

        Schema::create($this->dwsCertification, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害福祉サービス受給者証ID');
            $table->references('user', '利用者');
            $table->createdAt();
        });
        Schema::create($this->dwsCertificationAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害福祉サービス受給者証属性ID');
            $table->references($this->dwsCertification, '障害福祉サービス受給者証ID');
            $table->catalogued($this->dwsLevel, '障害支援区分');
            $table->catalogued($this->dwsCertificationStatus, '障害福祉サービス認定区分', 'status');
            $table->catalogued($this->copayCoordinationType, '上限管理区分');
            $table->string('child_family_name', 100)->comment('児童の姓');
            $table->string('child_given_name', 100)->comment('児童の氏名');
            $table->string('child_phonetic_family_name', 100)->comment('フリガナ：児童の姓');
            $table->string('child_phonetic_given_name', 100)->comment('フリガナ：児童の氏名');
            $table->string('dws_number', 10)->charset('binary')->comment('受給者証番号');
            $table->string('city_code', 6)->charset('binary')->comment('市区町村番号');
            $table->string('city_name', 100)->comment('市区町村名');
            $table->integer('copay_rate')->comment('利用者負担割合（原則）');
            $table->integer('copay_limit')->comment('負担上限月額');
            $table->integer('copay_coordination_office_id')->nullable()->comment('上限管理事業所ID');
            $table->boolean('is_subject_of_comprehensive_support')->comment('重度障害者等包括支援対象フラグ');
            $table->date('activated_on')->comment('認定の有効期間（開始）');
            $table->date('deactivated_on')->comment('認定の有効期間（終了）');
            $table->date('issued_on')->comment('交付日');
            $table->date('effectivated_on')->comment('適用日');
            $table->date('copay_activated_on')->comment('利用者負担適用期間（開始）');
            $table->date('copay_deactivated_on')->comment('利用者負担適用期間（終了）');
            $table->date('child_birthday')->nullable()->comment('児童の生年月日');
            $table->attr($this->dwsCertification);
        });
        Schema::createAttrIntermediate($this->dwsCertification, '障害福祉サービス受給者証');
        Schema::createAttrTriggers($this->dwsCertification);
        Schema::createCatalogueIntermediate($this->dwsCertificationAttr, $this->dwsType, '障害福祉サービス受給者証属性', '障害種別');

        Schema::create($this->dwsCertificationAgreement, function (Blueprint $table): void {
            // COLUMNS
            $table->references($this->dwsCertificationAttr, '障害福祉サービス受給者証属性')->onDelete('cascade');
            $table->references('office', '事業所');
            $table->integer('index_number')->comment('番号');
            // 外部キー制約名が長すぎてMYSQLのエラーになるので catalogued を使用しないで外部キー制約名を指定する
            $table->integer($this->dwsCertificationAgreementType)->unsigned()->comment('サービス内容');
            $table->foreign($this->dwsCertificationAgreementType, 'dws_certification_agreement_above_type_foreign')
                ->references('id')
                ->on($this->dwsCertificationAgreementType);
            $table->integer('payment_amount')->comment('契約支給量');
            $table->date('agreed_on')->comment('契約日');
            $table->date('expired_on')->nullable()->comment('該当契約支給量によるサービス提供終了日');
            $table->sortOrder();
            // KEYS
            $table->primary(
                ["{$this->dwsCertificationAttr}_id", $this->sortOrder],
                "{$this->dwsCertificationAgreement}_{$this->sortOrder}_primary"
            );
        });

        Schema::create($this->dwsCertificationGrant, function (Blueprint $table): void {
            // COLUMNS
            $table->references($this->dwsCertificationAttr, '障害福祉サービス受給者証属性')->onDelete('cascade');
            $table->catalogued($this->dwsCertificationServiceType, '障害福祉サービス受給者証 サービス種別');
            $table->string('granted_amount')->comment('支給量等');
            $table->date('activated_on')->comment('認定の有効期間（開始）');
            $table->date('deactivated_on')->comment('認定の有効期間（終了）');
            $table->sortOrder();
            // KEYS
            $table->primary(
                ["{$this->dwsCertificationAttr}_id", $this->sortOrder],
                "{$this->dwsCertificationGrant}_{$this->sortOrder}_primary"
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->dwsCertificationGrant);
        Schema::dropIfExists($this->dwsCertificationAgreement);
        Schema::dropAttrTriggers($this->dwsCertification);
        Schema::dropAttrIntermediate($this->dwsCertification);
        Schema::dropCatalogueIntermediate($this->dwsCertificationAttr, $this->dwsType);
        Schema::dropIfExists($this->dwsCertificationAttr);
        Schema::dropIfExists($this->dwsCertification);
        Schema::dropIfExists($this->dwsLevel);
        Schema::dropIfExists($this->dwsType);
        Schema::dropIfExists($this->dwsCertificationAgreementType);
        Schema::dropIfExists($this->dwsCertificationServiceType);
        Schema::dropIfExists($this->copayCoordinationType);
    }

    /**
     * 障害福祉サービス受給者証 サービス内容の定義一覧.
     *
     * @return array
     */
    private function dwsCertificationAgreementType(): array
    {
        return [
            [11, 'physicalCare'],
            [12, 'housework'],
            [13, 'accompanyWithPhysicalCare'],
            [14, 'accompany'],
            [15, 'accessibleTaxi'],
            [21, 'visitingCareForPwsd1'],
            [22, 'visitingCareForPwsd2'],
            [23, 'visitingCareForPwsd3'],
            [29, 'outingSupportForPwsd'],
        ];
    }

    /**
     * 障害福祉サービス受給者証 サービス種別の定義一覧.
     *
     * @return array
     */
    private function dwsCertificationServiceType(): array
    {
        return [
            [1, 'physicalCare'],
            [2, 'housework'],
            [3, 'accompanyWithPhysicalCare'],
            [4, 'accompany'],
            [6, 'visitingCareForPwsd'],
        ];
    }

    /**
     * 障害支援区分の定義一覧.
     *
     * @return array
     */
    private function dwsLevel(): array
    {
        return [
            [99, 'notApplicable'],
            [21, 'level1'],
            [22, 'level2'],
            [23, 'level3'],
            [24, 'level4'],
            [25, 'level5'],
            [26, 'level6'],
        ];
    }

    /**
     * 障害種別の定義一覧.
     *
     * @return array
     */
    private function dwsType(): array
    {
        return [
            [1, 'physical'],
            [2, 'intellectual'],
            [3, 'mental'],
            [5, 'intractableDiseases'],
        ];
    }

    /**
     * 上限管理区分の定義一覧.
     *
     * @return array
     */
    private function copayCoordinationType(): array
    {
        return [
            [1, 'none'],
            [2, 'internal'],
            [3, 'external'],
            [9, 'unknown'],
        ];
    }
}
