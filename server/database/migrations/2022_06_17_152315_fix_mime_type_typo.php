<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * mime_type の typo を修正する.
 */
class FixMimeTypeTypo extends Migration
{
    private const DWS_BILLING_FILE = 'dws_billing_file';
    private const LTCS_BILLING_FILE = 'ltcs_billing_file';
    private const MIME_TYPE = 'mime_type';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // mime_type テーブルがなかったため追加
        Schema::createStringCatalogue(self::MIME_TYPE, 'MimeType', $this->mimeTypes());

        DB::table(self::DWS_BILLING_FILE)
            ->where('mime_type', 'applicatipn/pdf')
            ->update(['mime_type' => 'application/pdf']);
        Schema::table(self::DWS_BILLING_FILE, function (Blueprint $table): void {
            $table->string(self::MIME_TYPE, 100)
                ->charset('binary')
                ->collation('binary')
                ->comment(self::MIME_TYPE)
                ->change();
            $table->foreign(self::MIME_TYPE)->references('id')->on(self::MIME_TYPE);
        });

        DB::table(self::LTCS_BILLING_FILE)
            ->where('mime_type', 'applicatipn/pdf')
            ->update(['mime_type' => 'application/pdf']);
        Schema::table(self::LTCS_BILLING_FILE, function (Blueprint $table): void {
            $table->string(self::MIME_TYPE, 100)
                ->charset('binary')
                ->collation('binary')
                ->comment(self::MIME_TYPE)
                ->change();
            $table->foreign(self::MIME_TYPE)->references('id')->on(self::MIME_TYPE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::LTCS_BILLING_FILE, function (Blueprint $table): void {
            $table->dropForeign(self::LTCS_BILLING_FILE . '_' . self::MIME_TYPE . '_foreign');
        });
        Schema::table(self::LTCS_BILLING_FILE, function (Blueprint $table): void {
            $table->string(self::MIME_TYPE, 20)
                ->comment('MimeType')
                ->change();
        });
        DB::table(self::LTCS_BILLING_FILE)
            ->where('mime_type', 'application/pdf')
            ->update(['mime_type' => 'applicatipn/pdf']);

        Schema::table(self::DWS_BILLING_FILE, function (Blueprint $table): void {
            $table->dropForeign(self::DWS_BILLING_FILE . '_' . self::MIME_TYPE . '_foreign');
        });
        Schema::table(self::DWS_BILLING_FILE, function (Blueprint $table): void {
            $table->string(self::MIME_TYPE, 20)
                ->comment('MimeType')
                ->change();
        });
        DB::table(self::DWS_BILLING_FILE)
            ->where('mime_type', 'application/pdf')
            ->update(['mime_type' => 'applicatipn/pdf']);

        Schema::dropIfExists(self::MIME_TYPE);
    }

    /**
     * MimeType の定義一覧.
     *
     * @return array
     */
    private function mimeTypes(): array
    {
        return [
            ['text/csv', 'CSV'],
            ['application/pdf', 'PDF'],
        ];
    }
}
