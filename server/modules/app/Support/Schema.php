<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use ScalikePHP\Seq;

/**
 * 拡張版スキーマビルダー.
 *
 * @mixin \Illuminate\Support\Facades\Schema
 * @codeCoverageIgnore Migration用の実装なのでUnitTest除外
 */
final class Schema
{
    /**
     * 属性中間テーブルを定義する.
     *
     * @param string $base
     * @param string $comment
     * @return void
     */
    public static function createAttrIntermediate(string $base, string $comment): void
    {
        self::create("{$base}_to_attr", function (Blueprint $table) use ($base, $comment): void {
            $attr = $base . '_attr';
            $baseKeyName = $table->buildForeignKeyName("{$base}_id");
            $attrKeyName = $table->buildForeignKeyName("{$attr}_id");
            $table->bigInteger("{$base}_id")->unsigned()->comment("{$comment}ID");
            $table->primary("{$base}_id", "{$base}_to_attr_primary");
            $table->bigInteger("{$attr}_id")->unsigned()->comment("{$comment}属性ID");
            $table->foreign("{$base}_id", $baseKeyName)->references('id')->on($base);
            $table->foreign("{$attr}_id", $attrKeyName)->references('id')->on($attr)->onDelete('cascade');
        });
    }

    /**
     * 属性テーブル用トリガーを作成する.
     *
     * @param string $base
     * @return void
     */
    public static function createAttrTriggers(string $base): void
    {
        self::createTrigger("after_insert_{$base}_attr", 'INSERT', "{$base}_attr", [
            "DELETE FROM {$base}_to_attr WHERE {$base}_id = NEW.{$base}_id",
            "INSERT INTO {$base}_to_attr VALUES (NEW.{$base}_id, NEW.id)",
        ]);
    }

    /**
     * 区分値用テーブルを作成する.
     *
     * @param string $name
     * @param string $comment
     * @param array $definitions
     * @return void
     */
    public static function createCatalogue(string $name, string $comment, array $definitions = []): void
    {
        self::create($name, function (Blueprint $table) use ($comment): void {
            $table->integer('id')->unsigned()->primary()->comment("{$comment}ID");
            $table->string('name', 100)->comment("{$comment}名");
        });
        self::seedCatalogue($name, $definitions);
    }

    /**
     * 区分値（文字列キー）テーブルを作成する.
     *
     * @param string $name
     * @param string $comment
     * @param array $definitions
     */
    public static function createStringCatalogue(string $name, string $comment, array $definitions = []): void
    {
        self::create($name, function (Blueprint $table) use ($comment): void {
            $table->string('id', 100)->charset('binary')->primary()->comment("{$comment}ID");
            $table->string('name', 100)->comment("{$comment}名");
        });
        self::seedStringCatalogue($name, $definitions);
    }

    /**
     * 区分値向け中間テーブルを作成する.
     *
     * @param string $from
     * @param string $fromComment
     * @param string $to
     * @param string $toComment
     * @return void
     */
    public static function createCatalogueIntermediate(
        string $from,
        string $to,
        string $fromComment,
        string $toComment
    ): void {
        self::create("{$from}_{$to}", function (Blueprint $table) use ($from, $fromComment, $to, $toComment): void {
            $table->references($from, $fromComment)->onDelete('cascade');
            $table->catalogued($to, $toComment);
            $table->primary(["{$from}_id", $to], "{$from}_{$to}_primary");
        });
    }

    /**
     * キーワード検索用のテーブルを作成する.
     *
     * @param string $base
     * @param string $comment
     * @param string[] $columns
     */
    public static function createKeywordIndexTable(string $base, string $comment, array $columns): void
    {
        self::create("{$base}_keyword", function (Blueprint $table) use ($base, $comment): void {
            $table->references($base, $comment)->onDelete('CASCADE');
            $table->text('keyword')->comment('キーワード');
            $table->primary("{$base}_id");
        });
        // Lumen のマイグレーションは MySQL の FULLTEXT INDEX に対応していないため ALTER TABLE 文を用いる
        DB::statement("ALTER TABLE {$base}_keyword ADD FULLTEXT INDEX {$base}_keyword (keyword) WITH PARSER ngram");
        $targets = Seq::fromArray($columns)->map(fn (string $x): string => "NEW.{$x}")->mkString(', ');
        self::createTrigger(
            "{$base}_replace_keyword",
            'INSERT',
            "{$base}_attr",
            ["REPLACE INTO `{$base}_keyword` ({$base}_id, keyword) VALUES (NEW.{$base}_id, CONCAT({$targets}))"]
        );
    }

    /**
     * プロシージャを作成する.
     *
     * @param string $name
     * @param string $params
     * @param string $statement
     * @return void
     */
    public static function createProcedure(string $name, string $params, string $statement): void
    {
        DB::unprepared(
            <<<__EOS__
            CREATE PROCEDURE `{$name}`({$params}) BEGIN
                {$statement}
            END
            __EOS__
        );
    }

    /**
     * 中間テーブルを作成する.
     *
     * @param string $from
     * @param string $fromComment
     * @param string $to
     * @param string $toComment
     * @return void
     */
    public static function createIntermediate(string $from, string $to, string $fromComment, string $toComment): void
    {
        self::create("{$from}_to_{$to}", function (Blueprint $table) use ($from, $fromComment, $to, $toComment): void {
            $table->references($from, $fromComment)->onDelete('cascade');
            $table->references($to, $toComment);
            $table->primary(["{$from}_id", "{$to}_id"], "{$from}_to_{$to}_primary");
        });
    }

    /**
     * トリガーを作成する.
     *
     * @param string $name
     * @param string $after
     * @param string $on
     * @param array|string[] $statements
     * @return void
     */
    public static function createTrigger(string $name, string $after, string $on, $statements): void
    {
        $statement = implode(';', $statements);
        DB::unprepared(
            <<<__EOS__
            CREATE TRIGGER `{$name}` AFTER {$after} ON `{$on}` FOR EACH ROW
            BEGIN
                {$statement};
            END
            __EOS__
        );
    }

    /**
     * 区分値のレコードを追加する.
     *
     * @param string $table
     * @param array $definitions
     * @return void
     */
    public static function seedCatalogue(string $table, array $definitions): void
    {
        DB::transaction(function () use ($table, $definitions): void {
            $rows = Seq::from(...$definitions)->map(function (array $row): array {
                [$id, $name] = $row;
                assert(is_int($id), 'id should be int otherwise use seedStringCatalogue() instead.');
                return compact('id', 'name');
            });
            DB::table($table)->insert([...$rows]);
        });
    }

    /**
     * 区分値（文字列キー）のレコードを追加する.
     *
     * @param string $table テーブル名
     * @param array $definitions 登録するレコード
     */
    public static function seedStringCatalogue(string $table, array $definitions): void
    {
        DB::transaction(function () use ($table, $definitions): void {
            $rows = Seq::from(...$definitions)->map(function (array $row): array {
                [$id, $name] = $row;
                assert(is_string($id), 'id should be string otherwise use seedCatalogue() instead.');
                return compact('id', 'name');
            });
            DB::table($table)->insert([...$rows]);
        });
    }

    /**
     * 属性中間テーブルを破棄する.
     *
     * @param string $base
     * @return void
     */
    public static function dropAttrIntermediate(string $base): void
    {
        self::dropIfExists("{$base}_to_attr");
    }

    /**
     * 区分値向け中間テーブルを破棄する.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public static function dropCatalogueIntermediate(string $from, string $to): void
    {
        self::dropIfExists("{$from}_{$to}");
    }

    /**
     * キーワード検索用テーブルを削除する.
     *
     * @param string $base
     * @return void
     */
    public static function dropKeywordIndexTable(string $base): void
    {
        self::dropIfExists("{$base}_keyword");
        self::dropTrigger("{$base}_replace_keyword");
    }

    /**
     * 属性テーブル用トリガーを破棄する.
     *
     * @param string $base
     * @return void
     */
    public static function dropAttrTriggers(string $base): void
    {
        self::dropTrigger("after_insert_{$base}_attr");
    }

    /**
     * プロシージャを破棄する.
     *
     * @param string $name
     * @return void
     */
    public static function dropProcedure(string $name): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `{$name}`");
    }

    /**
     * 中間テーブルを破棄する.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public static function dropIntermediate(string $from, string $to): void
    {
        self::dropIfExists("{$from}_to_{$to}");
    }

    /**
     * トリガーを破棄する.
     *
     * @param string $name
     * @return void
     */
    public static function dropTrigger(string $name): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `{$name}`");
    }

    /**
     * 区分値のレコードを削除する.
     *
     * @param string $table
     * @param array $definitions
     */
    public static function unseedCatalogue(string $table, array $definitions): void
    {
        DB::transaction(function () use ($table, $definitions): void {
            $ids = Seq::from(...$definitions)->map(fn (array $row) => $row[0]);
            DB::table($table)->whereIn('id', [...$ids])->delete();
        });
    }

    /**
     * Get a schema builder instance for a connection.
     *
     * @param null|string $name
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name)
    {
        return app('db')->connection($name)->getSchemaBuilder();
    }

    /**
     * Dynamically handle calls to the class.
     */
    private static function getBuilder(): Builder
    {
        return app('db')->connection()->getSchemaBuilder();
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::getBuilder(), $name], $arguments);
    }
}
