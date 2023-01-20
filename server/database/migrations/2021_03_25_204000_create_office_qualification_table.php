<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 事業所：指定区分マスタテーブルを追加する.
 */
final class CreateOfficeQualificationTable extends Migration
{
    private const OFFICE_QUALIFICATION = 'office_qualification';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createStringCatalogue(self::OFFICE_QUALIFICATION, '事業所：指定区分', $this->officeQualifications());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::OFFICE_QUALIFICATION);
    }

    /**
     * 事業所：指定区分の定義一覧.
     *
     * @return array
     */
    private function officeQualifications(): array
    {
        return [
            ['1011', '居宅介護（障害福祉サービス）'],
            ['1012', '重度訪問介護（障害福祉サービス）'],
            ['1072', '移動支援（障害福祉サービス・地域生活支援）'],
            ['10ZZ', 'その他障害福祉サービス'],
            ['2011', '訪問介護（介護保険サービス）'],
            ['2046', '居宅介護支援（介護保険サービス）'],
            ['20A0', '訪問型サービス（介護保険サービス・総合事業）'],
            ['20ZZ', 'その他介護保険サービス'],
        ];
    }
}
