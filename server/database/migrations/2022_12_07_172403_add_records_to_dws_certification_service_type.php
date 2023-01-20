<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 障害福祉サービス受給者証：サービス種別 に重度訪問介護3種を追加する.
 *
 * @noinspection PhpUnused
 */
final class AddRecordsToDwsCertificationServiceType extends Migration
{
    private const DWS_CERTIFICATION_SERVICE_TYPE = 'dws_certification_service_type';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::DWS_CERTIFICATION_SERVICE_TYPE, $this->types());
        DB::update('
            UPDATE
                dws_certification_grant AS g
                LEFT JOIN dws_certification_attr AS a ON g.dws_certification_attr_id = a.id
            SET
                dws_certification_service_type = 7
            WHERE
                g.dws_certification_service_type = 6
                AND a.dws_level = 26
                AND a.is_subject_of_comprehensive_support = 1
        ');
        DB::update('
            UPDATE
                dws_certification_grant AS g
                LEFT JOIN dws_certification_attr AS a ON g.dws_certification_attr_id = a.id
            SET
                dws_certification_service_type = 8
            WHERE
                g.dws_certification_service_type = 6
                AND a.dws_level = 26
                AND a.is_subject_of_comprehensive_support != 1
        ');
        DB::update('
            UPDATE
                dws_certification_grant AS g
                LEFT JOIN dws_certification_attr AS a ON g.dws_certification_attr_id = a.id
            SET
                dws_certification_service_type = 9
            WHERE
                g.dws_certification_service_type = 6
                AND a.dws_level != 26
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::update('
            UPDATE
                dws_certification_grant
            SET
                dws_certification_service_type = 6
            WHERE
                dws_certification_service_type IN (7, 8, 9)
        ');
        Schema::unseedCatalogue(self::DWS_CERTIFICATION_SERVICE_TYPE, $this->types());
    }

    /**
     * 追加する障害福祉サービス受給者証：サービス種別の一覧.
     *
     * @return array
     */
    private function types(): array
    {
        return [
            [7, 'visitingCareForPwsd1'],
            [8, 'visitingCareForPwsd2'],
            [9, 'visitingCareForPwsd3'],
        ];
    }
}
