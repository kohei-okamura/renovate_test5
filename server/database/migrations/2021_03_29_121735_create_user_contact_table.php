<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 利用者連絡先電話番号テーブルを追加する.
 */
final class CreateUserContactTable extends Migration
{
    private const USER_CONTACT = 'user_contact';
    private const USER_ATTR = 'user_attr';
    private const CONTACT_RELATIONSHIP = 'contact_relationship';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::table(self::USER_ATTR, function (Blueprint $table) {
            $table->dropColumn('tel');
            $table->dropColumn('fax');
        });
        Schema::createCatalogue(self::CONTACT_RELATIONSHIP, '連絡先電話番号：続柄・関係', $this->contactRelationships());
        Schema::create(self::USER_CONTACT, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('利用者連絡先電話番号ID');
            $table->references(self::USER_ATTR, '利用者属性')->onDelete('cascade');
            $table->sortOrder();
            $table->tel();
            $table->catalogued(self::CONTACT_RELATIONSHIP, '続柄・関係', 'relationship');
            $table->string('name')->comment('名前');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::USER_CONTACT);
        Schema::dropIfExists(self::CONTACT_RELATIONSHIP);
        if (!Schema::hasColumn(self::USER_ATTR, 'fax')) {
            Schema::table(self::USER_ATTR, function (Blueprint $table): void {
                $table->fax()->after('location');
            });
        }
        if (!Schema::hasColumn(self::USER_ATTR, 'tel')) {
            Schema::table(self::USER_ATTR, function (Blueprint $table): void {
                $table->tel()->after('location');
            });
        }
    }

    /**
     * 続柄・関係の定義一覧.
     *
     * @return array
     */
    private function contactRelationships(): array
    {
        return [
            [10, '本人'],
            [20, '家族'],
            [30, '弁護士'],
            [99, 'その他'],
        ];
    }
}
