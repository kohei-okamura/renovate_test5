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
 * 契約テーブルを作成する.
 */
class CreateContractTable extends Migration
{
    private $contract = 'contract';
    private $contractAttr = 'contract_attr';
    private $contractStatus = 'contract_status';
    private $office = 'office';
    private $organization = 'organization';
    private $serviceSegment = 'service_segment';
    private $user = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->contractStatus, '契約状態', $this->contractStatuses());
        Schema::create($this->contract, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('契約ID');
            $table->references($this->organization, '事業者');
            $table->references($this->user, '利用者');
            $table->references($this->office, '事業所');
            $table->createdAt();
        });
        Schema::create($this->contractAttr, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('契約属性ID');
            $table->references($this->contract, '契約ID');
            $table->catalogued($this->serviceSegment, '事業領域');
            $table->catalogued($this->contractStatus, '契約状態', 'status');
            $table->date('contracted_on')->nullable()->comment('契約日');
            $table->date('terminated_on')->nullable()->comment('解約日');
            $table->string('terminated_reason')->comment('解約理由');
            $table->attr($this->contract);
        });
        Schema::createAttrIntermediate($this->contract, '契約');
        Schema::createAttrTriggers($this->contract);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->contract);
        Schema::dropAttrIntermediate($this->contract);
        Schema::dropIfExists($this->contractAttr);
        Schema::dropIfExists($this->contract);
        Schema::dropIfExists($this->contractStatus);
    }

    /**
     * 契約状態の定義一覧.
     *
     * @return array
     */
    private function contractStatuses(): array
    {
        return [
            [1, '仮契約'],
            [2, '本契約'],
            [3, '契約終了'],
            [9, '無効'],
        ];
    }
}
