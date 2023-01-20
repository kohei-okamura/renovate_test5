<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console\Commands;

use App\Console\Commands\ImportLtcsHomeVisitLongTermCareDictionaryCommand;
use Domain\Common\Carbon;
use Mockery;
use Symfony\Component\Console\Command\Command;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConsoleInputInterfaceMixin;
use Tests\Unit\Mixins\ImportLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Console\Commands\ImportLtcsHomeVisitLongTermCareDictionaryCommand} のテスト.
 */
final class ImportLtcsHomeVisitLongTermCareDictionaryCommandTest extends Test
{
    use ConsoleInputInterfaceMixin;
    use ImportLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private const COUNT = 604;
    private const FILEPATH = 'foo/bar/baz/qux.csv';
    private const ID = '1192';
    private const EFFECTIVATED_ON = '2008-05-17';
    private const NAME = '令和3年4月改訂版';

    private ImportLtcsHomeVisitLongTermCareDictionaryCommand $command;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (ImportLtcsHomeVisitLongTermCareDictionaryCommandTest $self): void {
            $self->importLtcsHomeVisitLongTermCareDictionaryUseCase
                ->allows('handle')
                ->andReturn(self::COUNT)
                ->byDefault();

            $self->inputInterface->allows('getArguments')->andReturn([]);
            $self->inputInterface->allows('getOptions')->andReturn([
                'dummyOption' => true,
            ]);
            $self->inputInterface->allows('getOption')->with('filepath')->andReturn(self::FILEPATH)->byDefault();
            $self->inputInterface->allows('getOption')->with('id')->andReturn(self::ID)->byDefault();
            $self->inputInterface->allows('getOption')
                ->with('effectivatedOn')
                ->andReturn(self::EFFECTIVATED_ON)
                ->byDefault();
            $self->inputInterface->allows('getOption')->with('name')->andReturn(self::NAME)->byDefault();

            $self->logger->allows('info')->byDefault();

            $self->command = app(ImportLtcsHomeVisitLongTermCareDictionaryCommand::class);
            $self->command->setInput($self->inputInterface);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_handle(): void
    {
        $this->should('fail when filepath option not given', function (): void {
            $this->inputInterface->expects('getOption')->with('filepath')->andReturn(null);
            $this->logger->expects('warning')->with('The "--filepath" option is required.', Mockery::any());

            $actual = app()->call([$this->command, 'handle']);

            $this->assertSame(Command::FAILURE, $actual);
        });
        $this->should('fail when id option not given', function (): void {
            $this->inputInterface->expects('getOption')->with('id')->andReturn(null);
            $this->logger->expects('warning')->with('The "--id" option is required.', Mockery::any());

            $actual = app()->call([$this->command, 'handle']);

            $this->assertSame(Command::FAILURE, $actual);
        });
        $this->should('fail when id option value is not numeric', function (): void {
            $this->inputInterface->expects('getOption')->with('id')->andReturn('あいうえお');
            $this->logger->expects('warning')->with('The "--id" option requires int value.', Mockery::any());

            $actual = app()->call([$this->command, 'handle']);

            $this->assertSame(Command::FAILURE, $actual);
        });
        $this->should('fail when effectivatedOn option not given', function (): void {
            $this->inputInterface->expects('getOption')->with('effectivatedOn')->andReturn(null);
            $this->logger->expects('warning')->with('The "--effectivatedOn" option is required.', Mockery::any());

            $actual = app()->call([$this->command, 'handle']);

            $this->assertSame(Command::FAILURE, $actual);
        });
        $this->should('fail when effectivatedOn option is not valid date', function (): void {
            $this->inputInterface->expects('getOption')->with('effectivatedOn')->andReturn('あいうえお');
            $this->logger->expects('warning')->with(
                'The "--effectivatedOn" option requires date value, e.g. 2021-04-01.',
                Mockery::any()
            );

            $actual = app()->call([$this->command, 'handle']);

            $this->assertSame(Command::FAILURE, $actual);
        });
        $this->should('fail when name option not given', function (): void {
            $this->inputInterface->expects('getOption')->with('name')->andReturn(null);
            $this->logger->expects('warning')->with('The "--name" option is required.', Mockery::any());

            $actual = app()->call([$this->command, 'handle']);

            $this->assertSame(Command::FAILURE, $actual);
        });
        $this->should('success when valid options given', function (): void {
            $actual = app()->call([$this->command, 'handle']);
            $this->assertSame(Command::SUCCESS, $actual);
        });
        $this->should('log that command is started', function (): void {
            $this->logger->expects('info')->with(
                '介護保険サービス：訪問介護：サービスコード辞書インポートコマンドを実行します',
                [
                    'command' => 'ltcs-home-visit-long-term-care-dictionary:import',
                    'arguments' => [],
                    'options' => ['dummyOption' => true],
                ]
            );

            app()->call([$this->command, 'handle']);
        });
        $this->should('log that command is completed', function (): void {
            $count = mt_rand();
            $this->importLtcsHomeVisitLongTermCareDictionaryUseCase->expects('handle')->andReturn($count);
            $this->logger->expects('info')->with(
                '介護保険サービス：訪問介護：サービスコード辞書インポートコマンドを実行しました',
                [
                    'command' => 'ltcs-home-visit-long-term-care-dictionary:import',
                    'arguments' => [],
                    'options' => ['dummyOption' => true],
                    'count' => $count,
                ]
            );

            app()->call([$this->command, 'handle']);
        });
        $this->should('call ImportLtcsHomeVisitLongTermCareDictionaryUseCase', function (): void {
            $this->importLtcsHomeVisitLongTermCareDictionaryUseCase
                ->expects('handle')
                ->withArgs(function (string $filepath, int $id, Carbon $effectivatedOn, string $name): bool {
                    return $filepath === self::FILEPATH
                        && $id === (int)self::ID
                        && $effectivatedOn->equalTo(Carbon::parse(self::EFFECTIVATED_ON))
                        && $name === self::NAME;
                })
                ->andReturn(self::COUNT);

            app()->call([$this->command, 'handle']);
        });
        $this->should('fail when options of filepath return not string', function () {
            $this->inputInterface
                ->allows('getOption')
                ->with('filepath')
                ->andReturn(true);
            $this->logger->allows('warning')->andReturnNull();

            $actual = app()->call([$this->command, 'handle']);
            $this->assertSame(Command::FAILURE, $actual);
        });
    }
}
