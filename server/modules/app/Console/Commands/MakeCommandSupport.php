<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Lib\Exceptions\RuntimeException;

/**
 * コード生成コマンド基底クラス.
 *
 * @codeCoverageIgnore 開発用ユーリティリティのためカバレッジ対象外とする
 */
trait MakeCommandSupport
{
    /**
     * 引数（Argument）から FQN を取得して整形する.
     *
     * @param string $key 引数の名前
     * @return string
     */
    protected function fqn(string $key): string
    {
        // 先頭にバックスラッシュがある場合は取り除く.
        $fqn = preg_replace('/\A\\\\/', '', $this->argument($key));
        if (class_exists('\\\\' . $fqn)) {
            throw new RuntimeException("Class not found: {$fqn}");
        }
        return $fqn;
    }

    /**
     * コマンドを実行する.
     *
     * @param array $params 置換前のキーワードをキー, 置換後の文字列を値とする連想配列
     * @param string $stub スタブ（テンプレート）のパス
     * @param string $path 出力先のパス
     * @return void
     */
    protected function make(array $params, string $stub, string $path): void
    {
        $content = str_replace(
            array_keys($params),
            array_values($params),
            file_get_contents($stub)
        );
        file_put_contents($path, $content);
    }
}
