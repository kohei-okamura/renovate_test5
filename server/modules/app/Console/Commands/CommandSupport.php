<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\ConsoleContext;
use Carbon\Exceptions\InvalidFormatException;
use Closure;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lib\Exceptions\InvalidConsoleOptionException;
use Lib\Logging;
use ScalikePHP\Option;

/**
 * コンソールコマンド向けサポート処理.
 *
 * @mixin \Illuminate\Console\Command
 */
trait CommandSupport
{
    use Logging;

    /**
     * 事業者コードをオプションから取得して {@link \Domain\Context\Context} を生成する.
     *
     * @param string $optionName
     * @return \Domain\Context\Context[]|\ScalikePHP\Option
     */
    protected function createContext(string $optionName): Option
    {
        return $this
            ->getStringOption($optionName)
            ->map(fn (string $organizationCode): Context => ConsoleContext::create($organizationCode));
    }

    /**
     * オプションから真偽値を取得する.
     *
     * @param string $name
     * @return bool[]|\ScalikePHP\Option
     */
    // 使用するコマンドができたら開通させる
//    protected function getBoolOption(string $name): Option
//    {
//        $value = $this->option($name);
//        return Option::from($value)->map(fn ($x): bool => is_bool($value) && $value);
//    }

    /**
     * オプションから日付を取得する.
     *
     * @param string $name
     * @throws \Lib\Exceptions\InvalidConsoleOptionException
     * @return \Domain\Common\Carbon[]|\ScalikePHP\Option
     */
    protected function getDateOption(string $name): Option
    {
        $value = $this->option($name);
        try {
            return Option::from($value)->map(fn ($x): Carbon => Carbon::parse($value)->startOfDay());
        } catch (InvalidFormatException $exception) {
            throw new InvalidConsoleOptionException(
                "The \"--{$name}\" option requires date value, e.g. 2021-04-01.",
                0,
                $exception
            );
        }
    }

    /**
     * オプションから整数値を取得する.
     *
     * @param string $name
     * @throws \Lib\Exceptions\InvalidConsoleOptionException
     * @return int[]|\ScalikePHP\Option
     */
    protected function getIntOption(string $name): Option
    {
        $value = $this->option($name);
        if ($value === null) {
            return Option::none();
        } elseif (is_numeric($value)) {
            return Option::some((int)$value);
        } else {
            throw new InvalidConsoleOptionException("The \"--{$name}\" option requires int value.");
        }
    }

    /**
     * オプションから文字列を取得する.
     *
     * @param string $name
     * @throws \Lib\Exceptions\InvalidConsoleOptionException
     * @return \ScalikePHP\Option|string[]
     */
    protected function getStringOption(string $name): Option
    {
        $value = $this->option($name);
        if ($value === null) {
            return Option::none();
        } elseif (is_string($value)) {
            return Option::some($value);
        } else {
            throw new InvalidConsoleOptionException("The \"--{$name}\" option requires string value.");
        }
    }

    /**
     * 指定されたクロージャーの実行前後にログを出力する.
     *
     * @param string $name
     * @param \Closure $f
     * @param array $optionMasks ログでマスクするオプション [option名=>マスク文字列, ...]
     * @return int
     */
    protected function withLogging(string $name, Closure $f, array $optionMasks = []): int
    {
        $logger = $this->logger();
        $options = $this->options();
        foreach ($optionMasks as $option => $mask) {
            if (Arr::exists($options, $option)) {
                Arr::set($options, $option, $mask);
            }
        }
        $context = [
            'command' => $this->name,
            'arguments' => $this->arguments(),
        ] + compact('options');

        $logger->info("{$name}を実行します", $context);
        try {
            $count = $f();
            $logger->info("{$name}を実行しました", $context + compact('count'));
            return Command::SUCCESS;
        } catch (InvalidConsoleOptionException $exception) {
            $this->logger()->warning($exception->getMessage(), $context);
            return Command::FAILURE;
        }
    }
}
