<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api;

use ApiTester;
use Domain\ModelCompat;
use Illuminate\Support\Arr;
use Lib\Json;
use ReflectionClass;
use ReflectionMethod;
use ScalikePHP\Seq;
use Tests\Unit\Fixtures;

/**
 * APITestSuite 基底クラス.
 */
abstract class Test implements Fixtures
{
    protected const COMMAND_SUCCESS = 0;
    protected const COMMAND_FAILURE = 1;

    /**
     * 共通セットアップ処理
     *
     * @param \ApiTester $I
     */
    public function _before(ApiTester $I)
    {
        $I->resetAssertLog();

        $I->haveHttpHeader('Host', 'eustylelab1.zinger-e2e.test');

        $I->setUpSnapshotIncrementor();
        $I->setTargetClass($this);

        // Mixin処理実行
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (strpos($method->getName(), '_beforeMixin') === 0) {
                $method->invoke($this, $I);
            }
        }
    }

    /**
     * 共通終了処理
     *
     * @param \ApiTester $I
     */
    public function _after(ApiTester $I)
    {
        // Mixin処理実行
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (strpos($method->getName(), '_afterMixin') === 0) {
                $method->invoke($this, $I);
            }
        }
    }

    /**
     * テスト成功時終了処理.
     *
     * @param ApiTester $I
     */
    public function _passed(ApiTester $I)
    {
        $I->checkAssertLog();
    }

    /**
     * DomainモデルからArrayを生成する（リクエストパラメータ用 または レスポンスコンテント検証用）.
     *
     * @param array|\Domain\ModelCompat|\ScalikePHP\Seq $entity
     * @throws \JsonException
     * @return array
     */
    protected function domainToArray(array|ModelCompat|Seq $entity): array
    {
        return Json::decode(Json::encode($entity), true);
    }

    /**
     * クエリパラメータを組み立てる.
     *
     * @param array $params
     * @return string
     */
    protected function buildQueryString(array $params): string
    {
        return '?' . Arr::query($params);
    }

    // util

    /**
     * 清音へ変換した値を返す. nameによるソートのためのUtil
     *
     * NOTE: 他のAPIでも使用する可能性が出たら実装位置を親クラスへ移動
     *
     * @param string $a
     * @return string
     */
    protected function replace_to_seion(string $a): string
    {
        return str_replace(
            [
                'が',
                'ぎ',
                'ぐ',
                'げ',
                'ご',
                'ざ',
                'じ',
                'ず',
                'ぜ',
                'ぞ',
                'だ',
                'ぢ',
                'づ',
                'で',
                'ど',
                'ば',
                'び',
                'ぶ',
                'べ',
                'ぼ',
                'ぱ',
                'ぴ',
                'ぷ',
                'ぺ',
                'ぽ',
                'ガ',
                'ギ',
                'グ',
                'ゲ',
                'ゴ',
                'ザ',
                'ジ',
                'ズ',
                'ゼ',
                'ゾ',
                'ダ',
                'ヂ',
                'ヅ',
                'デ',
                'ド',
                'バ',
                'ビ',
                'ブ',
                'ベ',
                'ボ',
                'パ',
                'ピ',
                'プ',
                'ペ',
                'ポ',
            ],
            [
                'か',
                'き',
                'く',
                'け',
                'こ',
                'さ',
                'し',
                'す',
                'せ',
                'そ',
                'た',
                'ち',
                'つ',
                'て',
                'と',
                'は',
                'ひ',
                'ふ',
                'へ',
                'ほ',
                'は',
                'ひ',
                'ふ',
                'へ',
                'ほ',
                'カ',
                'キ',
                'ク',
                'ケ',
                'コ',
                'サ',
                'シ',
                'ス',
                'セ',
                'ソ',
                'タ',
                'チ',
                'ツ',
                'テ',
                'ト',
                'ハ',
                'ヒ',
                'フ',
                'ヘ',
                'ホ',
                'ハ',
                'ヒ',
                'フ',
                'ヘ',
                'ホ',
            ],
            $a
        );
    }
}
