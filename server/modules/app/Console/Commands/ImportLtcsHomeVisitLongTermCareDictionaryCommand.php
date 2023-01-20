<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lib\Exceptions\InvalidConsoleOptionException;
use Symfony\Component\Console\Input\InputOption;
use UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryUseCase;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書一括インポートコマンド.
 */
final class ImportLtcsHomeVisitLongTermCareDictionaryCommand extends Command
{
    use CommandSupport;

    private const OPTION_FILEPATH = 'filepath';
    private const OPTION_ID = 'id';
    private const OPTION_EFFECTIVATED_ON = 'effectivatedOn';
    private const OPTION_NAME = 'name';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'ltcs-home-visit-long-term-care-dictionary:import';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '介護保険サービス：訪問介護：サービスコード辞書インポート';

    /**
     * コマンドを実行する.
     *
     * @param \UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryUseCase $useCase
     * @throws \Lib\Exceptions\RuntimeException|\Throwable
     * @return int
     */
    public function handle(ImportLtcsHomeVisitLongTermCareDictionaryUseCase $useCase): int
    {
        return $this->withLogging(
            '介護保険サービス：訪問介護：サービスコード辞書インポートコマンド',
            fn (): int => $useCase->handle(
                $this->getStringOption(self::OPTION_FILEPATH)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--filepath" option is required.');
                }),
                $this->getIntOption(self::OPTION_ID)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--id" option is required.');
                }),
                $this->getDateOption(self::OPTION_EFFECTIVATED_ON)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--effectivatedOn" option is required.');
                }),
                $this->getStringOption(self::OPTION_NAME)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--name" option is required.');
                })
            )
        );
    }

    /** {@inheritdoc} */
    protected function getOptions(): array
    {
        return [
            [self::OPTION_FILEPATH, 'f', InputOption::VALUE_REQUIRED, 'インポートする CSV ファイルのパス'],
            [self::OPTION_ID, null, InputOption::VALUE_REQUIRED, '辞書 ID'],
            [self::OPTION_EFFECTIVATED_ON, 'e', InputOption::VALUE_REQUIRED, '適用開始日 (e.g. 2020-04-01)'],
            [self::OPTION_NAME, 'N', InputOption::VALUE_REQUIRED, '辞書の名前'],
        ];
    }
}
