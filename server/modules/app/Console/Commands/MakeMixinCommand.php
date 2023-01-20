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
use Mockery\MockInterface;
use ScalikePHP\Seq;
use Symfony\Component\Console\Input\InputArgument;

/**
 * テスト用ミックスイン生成コマンド.
 *
 * @codeCoverageIgnore 開発用ユーリティリティのためカバレッジ対象外とする
 */
final class MakeMixinCommand extends Command
{
    use CommandSupport;
    use MakeCommandSupport;

    private const ARGUMENT_CLASSPATH = 'classpath';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'make:mixin';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = 'テスト用ミックスイン生成';

    /**
     * コマンドを実行する.
     *
     * @return int
     */
    public function handle(): int
    {
        $fqn = $this->fqn(self::ARGUMENT_CLASSPATH);
        $className = basename(str_replace('\\', '/', $fqn));
        $mixinName = $className . 'Mixin';

        $params = [
            '{{ className }}' => $className,
            '{{ fqn }}' => $fqn,
            '{{ imports }}' => Seq::from('Mockery', $fqn)->sortBy(fn (string $x): string => $x)
                ->map(fn (string $x): string => 'use ' . $x . ';')
                ->mkString("\n"),
            '{{ propertyName }}' => Str::camel($className),
            '{{ propertyType }}' => Seq::from(MockInterface::class, $fqn)
                ->sortBy(fn (string $x): string => $x)
                ->map(fn (string $x): string => '\\' . $x)
                ->mkString('|'),
            '{{ year }}' => Carbon::today()->year,
        ];
        $stub = resource_path('stubs/mixin.stub');
        $path = base_path("tests/Unit/Mixins/{$mixinName}.php");

        $this->make($params, $stub, $path);

        return Command::SUCCESS;
    }

    /** {@inheritdoc} */
    protected function getArguments(): array
    {
        return [
            [self::ARGUMENT_CLASSPATH, InputArgument::REQUIRED, 'テスト用ミックスイン対象のクラス（FQN）'],
        ];
    }
}
