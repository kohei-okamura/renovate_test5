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
use UseCase\Office\ImportOfficeUseCase;

/**
 * 事業所 CSV インポートコマンド.
 *
 * @codeCoverageIgnore 初回リリース時のテスト向けコマンドのため.
 *
 * 初回リリース後、事業所導入時に流用する場合はテストを追加すること.
 */
final class ImportOfficeCommand extends Command
{
    use CommandSupport;

    private const OPTION_FILEPATH = 'filepath';
    private const OPTION_ORGANIZATION = 'organization';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'office:import';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '事業所インポート';

    /**
     * コマンドを実行する.
     *
     * @param \UseCase\Office\ImportOfficeUseCase $useCase
     * @return int
     */
    public function handle(ImportOfficeUseCase $useCase): int
    {
        return $this->withLogging(
            '事業所インポートコマンド',
            function () use ($useCase): int {
                $useCase->handle(
                    $this->createContext(self::OPTION_ORGANIZATION)->getOrElse(function (): void {
                        throw new InvalidConsoleOptionException('The "--organization" option is required.');
                    }),
                    $this->getStringOption(self::OPTION_FILEPATH)->getOrElse(function (): void {
                        throw new InvalidConsoleOptionException('The "--filepath" option is required.');
                    })
                );
                return self::SUCCESS;
            }
        );
    }

    /** {@inheritdoc} */
    protected function getOptions(): array
    {
        return [
            [self::OPTION_FILEPATH, 'f', InputOption::VALUE_REQUIRED, 'インポートする CSV ファイルのパス'],
            [self::OPTION_ORGANIZATION, 'o', InputOption::VALUE_REQUIRED, '事業者コード'],
        ];
    }
}
