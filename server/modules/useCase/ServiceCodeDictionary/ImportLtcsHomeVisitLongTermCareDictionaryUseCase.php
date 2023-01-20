<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書一括インポートユースケース.
 */
interface ImportLtcsHomeVisitLongTermCareDictionaryUseCase
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書を CSV ファイルから読み込んで登録する.
     *
     * ## FYI
     * 事業者（Organization）に依存しないためコンテキストを受け取らずに処理を行う.
     *
     * @param string $filepath
     * @param int $id
     * @param \Domain\Common\Carbon $effectivatedOn
     * @param string $name
     * @throws \Throwable
     * @return int 登録されたエントリの数
     */
    public function handle(string $filepath, int $id, Carbon $effectivatedOn, string $name): int;
}
