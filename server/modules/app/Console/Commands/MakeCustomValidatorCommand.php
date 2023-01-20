<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Common\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * カスタムバリデータ用Rule trait生成コマンド.
 *
 * @codeCoverageIgnore 開発用ユーティリティのため
 */
final class MakeCustomValidatorCommand extends Command
{
    use CommandSupport;
    use MakeCommandSupport;

    private const ARGUMENT_NAME = 'name';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'make:validator';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = 'カスタムバリデータtrait生成';

    /**
     * コマンドを実行する.
     *
     * @return int
     */
    public function handle(): int
    {
        $ruleName = $this->argument(self::ARGUMENT_NAME);
        $className = Str::studly($ruleName) . 'Rule';
        $methodName = 'validate' . Str::studly($ruleName);

        $params = [
            '{{ className }}' => $className,
            '{{ methodName }}' => $methodName,
            '{{ year }}' => Carbon::today()->year,
        ];
        $stub = resource_path('stubs/validator-rule.stub');
        $fileName = "modules/app/Validations/Rules/{$className}.php";
        $path = base_path($fileName);

        $this->make($params, $stub, $path);

        $this->info("Making {$fileName} succeed.");
        return Command::SUCCESS;
    }

    /** {@inheritdoc} */
    protected function getArguments(): array
    {
        return [
            [self::ARGUMENT_NAME, InputArgument::REQUIRED, 'バリデーション名'],
        ];
    }
}
