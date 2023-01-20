<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryUseCase;

/**
 * 障害福祉サービス：居宅介護：サービスコード辞書インポートコマンド.
 */
class ImportDwsHomeHelpServiceDictionaryCommand extends Command
{
    use CommandSupport;
    use ImportDictionaryCommandSupport;

    /**
     * コンソールコマンドの名前と引数、オプション.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        dws-home-help-service-dictionary:import
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
    protected $description = '障害福祉サービス：居宅介護：サービスコード辞書インポート';

    /**
     * コンソールコマンドの実行.
     *
     * @param \UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryUseCase $useCase
     * @return int
     */
    public function handle(ImportDwsHomeHelpServiceDictionaryUseCase $useCase): int
    {
        return $this->withLogging(
            '障害福祉サービス：居宅介護：サービスコード辞書インポートコマンド',
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
