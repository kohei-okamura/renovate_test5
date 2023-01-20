<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Common\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * テスト用ミックスイン生成コマンド.
 *
 * @codeCoverageIgnore 開発用ユーリティリティのためカバレッジ対象外とする
 */
final class MakeUnitTestCommand extends Command
{
    use CommandSupport;
    use MakeCommandSupport;

    private const ARGUMENT_CLASSPATH = 'classpath';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'make:test:unit';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '自動単体テスト生成';

    /**
     * コマンドを実行する.
     *
     * @return int
     */
    public function handle(): int
    {
        $fqn = $this->fqn(self::ARGUMENT_CLASSPATH);
        $path = str_replace('\\', '/', $fqn);
        $className = basename($path);
        $namespace = str_replace('/', '\\', dirname($path));

        $params = [
            '{{ className }}' => $className,
            '{{ fqn }}' => $fqn,
            '{{ namespace }}' => $namespace,
            '{{ year }}' => Carbon::today()->year,
        ];
        $stub = resource_path('stubs/test-unit.stub');
        $path = base_path("tests/Unit/{$path}Test.php");

        $this->make($params, $stub, $path);

        return Command::SUCCESS;
    }

    /** {@inheritdoc} */
    protected function getArguments(): array
    {
        return [
            [self::ARGUMENT_CLASSPATH, InputArgument::REQUIRED, 'テスト対象のクラス（FQN）'],
        ];
    }
}
