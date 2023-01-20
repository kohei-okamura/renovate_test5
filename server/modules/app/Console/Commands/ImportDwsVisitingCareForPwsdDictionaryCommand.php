<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lib\Logging;
use UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryUseCase;

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書インポートコマンド.
 */
class ImportDwsVisitingCareForPwsdDictionaryCommand extends Command
{
    use CommandSupport;
    use ImportDictionaryCommandSupport;
    use Logging;

    /**
     * コンソールコマンドの名前と引数、オプション.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        dws-visiting-care-for-pwsd-dictionary:import
            {id : 辞書ID}
            {filename : ファイル名}
            {effectivatedOn : 適用開始日 (2020/04/01|2020-04-01)}
            {name : 名前}
    SIGNATURE;

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '障害福祉サービス：重度訪問介護：サービスコード辞書インポート';

    /**
     * コンソールコマンドの実行.
     *
     * @param \UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryUseCase $useCase
     * @return int
     */
    public function handle(ImportDwsVisitingCareForPwsdDictionaryUseCase $useCase): int
    {
        return $this->withLogging(
            '障害福祉サービス：重度訪問介護：サービスコード辞書インポートコマンド',
            function () use ($useCase): int {
                $id = $this->getId();
                $filepath = $this->getFilepath();
                $effectivatedOn = $this->getEffectivatedOn();
                $name = $this->argument('name');
                return $useCase->handle($id, $filepath, $effectivatedOn, $name);
            }
        );
    }
}
