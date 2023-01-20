<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 障害福祉サービス受給者証：サービス種別から重度訪問介護を削除
 *
 * @noinspection PhpUnused
 */
final class DeleteRecordToDwsCertificationServiceType extends Migration
{
    private const DWS_CERTIFICATION_SERVICE_TYPE = 'dws_certification_service_type';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::unseedCatalogue(self::DWS_CERTIFICATION_SERVICE_TYPE, $this->types());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::seedCatalogue(self::DWS_CERTIFICATION_SERVICE_TYPE, $this->types());
    }

    /**
     * 削除する障害福祉サービス受給者証：サービス種別の一覧.
     *
     * @return array
     */
    private function types(): array
    {
        return [
            [6, 'visitingCareForPwsd'],
        ];
    }
}
