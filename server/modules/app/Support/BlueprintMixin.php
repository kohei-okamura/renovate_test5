<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

/**
 * Blueprint 拡張.
 *
 * @mixin \Illuminate\Database\Schema\Blueprint
 * @codeCoverageIgnore Migration用の実装なのでUnitTest除外
 */
final class BlueprintMixin
{
    /**
     * テーブル名を省略するかの閾値（文字数）.
     *
     * FKを作る際のキー名が64文字（InnoDBの制約）だが、それを超える場合にキー名の中のテーブル名を省略する.
     * その判定のための閾値
     */
    public const THRESHOLD_LENGTH_TABLE_NAME_ABBR = 56;

    public function __invoke(): void
    {
        /**
         * 住所カラム定義マクロ.
         *
         * @return void
         */
        Blueprint::macro('addr', function (string $prefix = '', string $commentPrefix = '', ?string $after = null, $defaultPrefecture = null): void {
            if ($after !== null) {
                $this->string("{$prefix}addr_postcode", 8)->charset('binary')->comment("{$commentPrefix}郵便番号")->after($after);
                $this->catalogued('prefecture', "{$commentPrefix}都道府県", "{$prefix}addr_prefecture", "{$prefix}addr_postcode", $defaultPrefecture);
                $this->string("{$prefix}addr_city", 200)->comment("{$commentPrefix}市区町村")->after("{$prefix}addr_prefecture");
                $this->string("{$prefix}addr_street", 200)->comment("{$commentPrefix}町名・番地")->after("{$prefix}addr_city");
                $this->string("{$prefix}addr_apartment", 200)->comment("{$commentPrefix}建物名など")->after("{$prefix}addr_street");
            } else {
                $this->string("{$prefix}addr_postcode", 8)->charset('binary')->comment("{$commentPrefix}郵便番号");
                $this->catalogued('prefecture', "{$commentPrefix}都道府県", "{$prefix}addr_prefecture", null, $defaultPrefecture);
                $this->string("{$prefix}addr_city", 200)->comment("{$commentPrefix}市区町村");
                $this->string("{$prefix}addr_street", 200)->comment("{$commentPrefix}町名・番地");
                $this->string("{$prefix}addr_apartment", 200)->comment("{$commentPrefix}建物名など");
            }
        });

        /**
         * 属性メタ情報定義マクロ.
         *
         * @param string $base
         * @return void
         */
        Blueprint::macro('attr', function (string $base): void {
            $this->boolean('is_enabled')->comment('有効フラグ');
            $this->integer('version')->comment('バージョン');
            $this->updatedAt();
            $this->index(["{$base}_id", 'is_enabled'], "{$base}_attr_is_enabled_index");
            $this->unique(["{$base}_id", 'version'], "{$base}_attr_version_unique");
        });

        /**
         * 生年月日カラム定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('birthday', function (string $prefix = '', string $commentPrefix = ''): ColumnDefinition {
            return $this->date("{$prefix}birthday")->comment("{$commentPrefix}生年月日");
        });

        /**
         * FK名を組み立てる
         *
         * @param string $column カラム名
         * @retunr string|null Blueprintに渡すkeyName
         */
        Blueprint::macro('buildForeignKeyName', function (string $column): ?string {
            return strlen($this->getTable()) + strlen($column) >= BlueprintMixin::THRESHOLD_LENGTH_TABLE_NAME_ABBR
                ? $this->getTable() . '_' . preg_replace('/_?([a-z])[a-z]*/', '$1', Str::snake($column)) . '_foreign'
                : null;
        });

        /**
         * 区分値向け関係（リレーション）定義マクロ.
         *
         * @param string $to 区分値テーブル名
         * @param string $comment カラムコメント
         * @param null|string $name カラム名（指定しない場合は区分値テーブル名）
         * @return \Illuminate\Support\Fluent
         */
        Blueprint::macro('catalogued', function (string $to, string $comment, string $name = null, ?string $after = null, $default = null): Fluent {
            $column = $name ?? $to;
            $keyName = $this->buildForeignKeyName($column);
            $columnDef = $this->integer($column)->unsigned()->comment($comment);
            if ($after !== null) {
                $columnDef->after($after);
            }
            if ($default !== null) {
                $columnDef->default($default);
            }
            return $this->foreign($column, $keyName)->references('id')->on($to);
        });

        /**
         * コードカラム定義マクロ.
         *
         * @param int $length
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro(
            'code',
            fn (int $length): ColumnDefinition => $this->string('code', $length)->charset('binary')
        );

        /**
         * 登録日時カラム定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('createdAt', fn (): ColumnDefinition => $this->dateTime('created_at')->comment('登録日時'));

        /**
         * メールアドレスカラム定義マクロ.
         *
         * @param string $comment
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('email', function (string $comment = 'メールアドレス'): ColumnDefinition {
            return $this->string('email', 255)->charset('binary')->comment($comment);
        });

        /**
         * 有効期限カラム定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('expiredAt', fn (): ColumnDefinition => $this->dateTime('expired_at')->comment('有効期限'));

        /**
         * FAX番号カラム定義マクロ.
         *
         * @param string $comment
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('fax', function (string $comment = 'FAX番号'): ColumnDefinition {
            return $this->string('fax', 13)->charset('binary')->comment($comment);
        });

        /**
         * 確定日時カラム定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('fixedAt', function (): ColumnDefinition {
            return $this->dateTime('fixed_at')->nullable()->comment('確定日時');
        });

        /**
         * 主キー(id)定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('id', fn (): ColumnDefinition => $this->bigIncrements('id'));

        /**
         * 位置情報カラム定義マクロ.
         *
         * @return void
         */
        Blueprint::macro('location', function (): void {
            $this->point('location')->comment('位置情報');
            $this->spatialIndex('location');
        });

        /**
         * パスワードカラム定義マクロ.
         *
         * @param string $comment
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('password', function (string $comment = 'パスワード'): ColumnDefinition {
            return $this->string('password_hash', 100)->charset('binary')->comment($comment);
        });

        /**
         * 関係（リレーション）定義マクロ.
         *
         * @param string $to
         * @param string $comment
         * @param null|string $name
         * @return \Illuminate\Support\Fluent
         */
        Blueprint::macro('references', function (string $to, string $comment, ?string $name = null): Fluent {
            $columnName = $name ?? "{$to}_id";
            $keyName = $this->buildForeignKeyName($columnName);
            $this->bigInteger($columnName)->unsigned()->comment("{$comment}ID");
            return $this->foreign($columnName, $keyName)->references('id')->on($to);
        });

        /**
         * サービスコードカラム定義マクロ.
         *
         * @return void
         */
        Blueprint::macro('serviceCode', function (): void {
            $this->string('service_code', 6)->comment('サービスコード');
            $this->string('service_division_code', 2)->comment('サービス種類コード');
            $this->string('service_category_code', 4)->comment('サービス項目コード');
        });

        /**
         * 性別カラム定義マクロ.
         *
         * @return \Illuminate\Support\Fluent
         */
        Blueprint::macro('sex', function (string $prefix = '', string $commentPrefix = ''): Fluent {
            return $this->catalogued('sex', "{$commentPrefix}性別", "{$prefix}sex");
        });

        /**
         * 表示順カラム定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('sortOrder', fn (): ColumnDefinition => $this->integer('sort_order')->comment('表示順'));

        /**
         * 区分値（文字列キー）向け関係（リレーション）定義マクロ.
         *
         * @param string $to
         * @param string $comment
         * @param null|string $name
         * @return \Illuminate\Support\Fluent
         */
        Blueprint::macro('stringCatalogued', function (string $to, string $comment, string $name = null): Fluent {
            $column = $name ?? $to;
            $keyName = $this->buildForeignKeyName($column);
            $this->string($column, 100)->charset('binary')->comment($comment);
            return $this->foreign($column, $keyName)->references('id')->on($to);
        });

        /**
         * 氏名カラム定義マクロ.
         *
         * @return void
         */
        Blueprint::macro('structuredName', function (string $prefix = '', string $commentPrefix = ''): void {
            $this->string("{$prefix}family_name", 100)->comment("{$commentPrefix}姓");
            $this->string("{$prefix}given_name", 100)->comment("{$commentPrefix}名");
            $this->string("{$prefix}phonetic_family_name", 100)->comment("{$commentPrefix}フリガナ：姓");
            $this->string("{$prefix}phonetic_given_name", 100)->comment("{$commentPrefix}フリガナ：名");
        });

        /**
         * 電話番号カラム定義マクロ.
         *
         * @param string $comment
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('tel', function (string $prefix = '', string $commentPrefix = ''): ColumnDefinition {
            return $this->string("{$prefix}tel", 13)->charset('binary')->comment("{$commentPrefix}電話番号");
        });

        /**
         * 更新日時カラム定義マクロ.
         *
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        Blueprint::macro('updatedAt', fn (): ColumnDefinition => $this->dateTime('updated_at')->comment('更新日時'));

        /**
         * 外部キー一覧取得マクロ.
         *
         * @return array
         */
        Blueprint::macro('listTableForeignKeys', function (): array {
            $conn = Schema::connection(null)->getConnection()->getDoctrineSchemaManager();
            return array_map(fn ($key) => $key->getName(), $conn->listTableForeignKeys($this->getTable()));
        });

        /**
         * 外部キー存在確認マクロ.
         *
         * @param string $identifier 外部キーの識別子
         * @return bool
         */
        Blueprint::macro('hasForeignKey', fn (string $identifier): bool => in_array($identifier, $this->listTableForeignKeys(), true));
    }
}
