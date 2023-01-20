<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console\Commands;

use App\Console\Commands\ImportDwsHomeHelpServiceDictionaryCommand;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConsoleInputInterfaceMixin;
use Tests\Unit\Mixins\ImportDwsHomeHelpServiceDictionaryUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Console\Commands\ImportDwsHomeHelpServiceDictionaryCommand} のテスト.
 */
class ImportDwsHomeHelpServiceDictionaryCommandTest extends Test
{
    use CarbonMixin;
    use ConsoleInputInterfaceMixin;
    use ExamplesConsumer;
    use ImportDwsHomeHelpServiceDictionaryUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private const SUCCESS = 0;
    private const FAILURE = 1;
    private DwsHomeHelpServiceDictionary $dwsHomeHelpServiceDictionary;
    private ImportDwsHomeHelpServiceDictionaryCommand $command;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportDwsHomeHelpServiceDictionaryCommandTest $self): void {
            $self->dwsHomeHelpServiceDictionary = $self->examples->dwsHomeHelpServiceDictionaries[0];
            $self->inputInterface->allows('getOptions')->andReturn([
                'dummyOption' => true,
            ]);
            $self->importDwsHomeHelpServiceDictionaryUseCase
                ->allows('handle')
                ->andReturn(1)
                ->byDefault();
            $self->inputInterface
                ->allows('getArgument')
                ->with('id')
                ->andReturn("{$self->dwsHomeHelpServiceDictionary->id}")
                ->byDefault();
            $self->inputInterface
                ->allows('getArgument')
                ->with('filename')
                ->andReturn('filename')
                ->byDefault();
            $self->inputInterface
                ->allows('getArgument')
                ->with('effectivatedOn')
                ->andReturn('2020/09/28')
                ->byDefault();
            $self->inputInterface
                ->allows('getArgument')
                ->with('name')
                ->andReturn('test_name')
                ->byDefault();
            $self->inputInterface
                ->allows('getArguments')
                ->andReturn([])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();
            $self->logger
                ->allows('warning')
                ->andReturnNull()
                ->byDefault();
            $self->command = app(ImportDwsHomeHelpServiceDictionaryCommand::class);
            $self->command->setInput($self->inputInterface);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_handle(): void
    {
        $this->should('use ImportDwsHomeHelpServiceDictionaryUseCase', function (): void {
            $this->importDwsHomeHelpServiceDictionaryUseCase
                ->expects('handle')
                ->with(
                    $this->dwsHomeHelpServiceDictionary->id,
                    'filename',
                    '2020/09/28',
                    'test_name'
                )
                ->andReturn(2);
            $this->assertSame(
                self::SUCCESS,
                $this->command->handle($this->importDwsHomeHelpServiceDictionaryUseCase)
            );
        });
        $this->should('log that command is started', function (): void {
            $this->logger->expects('info')->with(
                '障害福祉サービス：居宅介護：サービスコード辞書インポートコマンドを実行します',
                [
                    'command' => 'dws-home-help-service-dictionary:import',
                    'arguments' => [],
                    'options' => ['dummyOption' => true],
                ]
            );

            $this->assertSame(
                self::SUCCESS,
                $this->command->handle($this->importDwsHomeHelpServiceDictionaryUseCase)
            );
        });
        $this->should('log that command is completed', function (): void {
            $count = mt_rand();
            $this->importDwsHomeHelpServiceDictionaryUseCase->expects('handle')->andReturn($count);
            $this->logger->expects('info')->with(
                '障害福祉サービス：居宅介護：サービスコード辞書インポートコマンドを実行しました',
                [
                    'command' => 'dws-home-help-service-dictionary:import',
                    'arguments' => [],
                    'options' => ['dummyOption' => true],
                    'count' => $count,
                ]
            );

            $this->assertSame(
                self::SUCCESS,
                $this->command->handle($this->importDwsHomeHelpServiceDictionaryUseCase)
            );
        });
        $this->should('fail when id is not valid', function (): void {
            $this->inputInterface
                ->allows('getArgument')
                ->with('id')
                ->andReturn('アイディ');
            $this->assertSame(
                self::FAILURE,
                $this->command->handle($this->importDwsHomeHelpServiceDictionaryUseCase)
            );
        });
        $this->should('fail when filename is not valid', function (): void {
            $this->inputInterface
                ->allows('getArgument')
                ->with('filename')
                ->andReturnNull();
            $this->assertSame(
                self::FAILURE,
                $this->command->handle($this->importDwsHomeHelpServiceDictionaryUseCase)
            );
        });
        $this->should('fail when effectivatedOn is not valid', function (): void {
            $this->inputInterface
                ->allows('getArgument')
                ->with('effectivatedOn')
                ->andReturn('20200928');
            $this->assertSame(
                self::FAILURE,
                $this->command->handle($this->importDwsHomeHelpServiceDictionaryUseCase)
            );
        });
    }
}
