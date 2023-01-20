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
use UseCase\User\ImportUserUseCase;

/**
 * 利用者 CSV 一括インポートコマンド.
 *
 * @codeCoverageIgnore テスト作業用コマンドのため
 */
final class ImportUserCommand extends Command
{
    use CommandSupport;

    private const OPTION_FILEPATH = 'filepath';
    private const OPTION_ORGANIZATION = 'organization';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'user:import';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '利用者インポート';

    /**
     * コマンドを実行する.
     *
     * @param \UseCase\User\ImportUserUseCase $useCase
     * @return int
     */
    public function handle(ImportUserUseCase $useCase): int
    {
        return $this->withLogging(
            '利用者インポートコマンド',
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
