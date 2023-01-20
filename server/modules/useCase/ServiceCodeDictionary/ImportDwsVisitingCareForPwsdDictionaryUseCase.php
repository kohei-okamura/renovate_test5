<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書インポートユースケース
 */
interface ImportDwsVisitingCareForPwsdDictionaryUseCase
{
    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書を登録する.
     *
     * @param int $id
     * @param string $filepath
     * @param string $effectivatedOn
     * @param string $name
     * @throws \Throwable
     * @return int 処理件数
     */
    public function handle(int $id, string $filepath, string $effectivatedOn, string $name): int;
}
