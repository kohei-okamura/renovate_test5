<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Support;

/**
 * Fake AliasLoader.
 *
 * laravel-ide-helper が生成する _ide_helper.php に余分なエイリアスが記載されることを防ぐ.
 * 実際には定義されないが `@mixin` で参照可能とするために `\Eloquent` のみ記載されるようにする.
 *
 * @see \Barryvdh\LaravelIdeHelper\Generator::getAliases()
 * @codeCoverageIgnore 開発環境用の設定なのでUnitTest除外
 */
class AliasLoader
{
    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return new static();
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [
            'Eloquent' => \Illuminate\Database\Eloquent\Model::class,
        ];
    }
}
