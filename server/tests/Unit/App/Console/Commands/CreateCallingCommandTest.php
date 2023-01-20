<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console\Commands;

use App\Console\Commands\CreateCallingCommand;
use App\Console\ConsoleContext;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConsoleInputInterfaceMixin;
use Tests\Unit\Mixins\CreateCallingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationIteratorMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Console\Commands\CreateCallingCommand} Test.
 */
class CreateCallingCommandTest extends Test
{
    use CarbonMixin;
    use ConsoleInputInterfaceMixin;
    use CreateCallingUseCaseMixin;
    use ExamplesConsumer;
    use OrganizationIteratorMixin;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private ConsoleContext $context;
    private CreateCallingCommand $command;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateCallingCommandTest $self): void {
            $self->context = new ConsoleContext($self->examples->organizations[0]);

            $self->organizationIterator
                ->allows('iterate')
                ->andReturnUsing(function (Closure $f) use ($self): void {
                    $f($self->context);
                })
                ->byDefault();
            $self->createCallingUseCase
                ->allows('handle')
                ->with($self->context, anInstanceOf(CarbonRange::class))
                ->andReturnNull();

            $self->command = app(CreateCallingCommand::class);
            $self->command->setInput($self->inputInterface);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_handle(): void
    {
        $this->should('return error if not specify `time` option when `batch` not specify', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('batch')
                ->andReturn(false);
            $this->inputInterface
                ->allows('getOption')
                ->with('time')
                ->andReturnNull();
            $this->logger
                ->allows('error')
                ->andReturnNull();

            $this->assertSame(
                CreateCallingCommand::FAILURE,
                $this->command->handle(
                    $this->organizationIterator,
                    $this->createCallingUseCase,
                ),
            );
        });

        $this->should('run normally with `batch` option', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('batch')
                ->andReturn(true);
            $this->createCallingUseCase
                ->allows('handle')
                ->with(
                    $this->context,
                    CarbonRange::create([
                        'start' => Carbon::now()->subMinutes(5)->subMinutes(120),
                        'end' => Carbon::now()->subMinutes(120),
                    ]),
                );

            $this->assertSame(
                CreateCallingCommand::SUCCESS,
                $this->command->handle(
                    $this->organizationIterator,
                    $this->createCallingUseCase,
                ),
            );
        });

        $this->should('run normally with `time` option', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('batch')
                ->andReturn(false);
            $this->inputInterface
                ->allows('getOption')
                ->with('time')
                ->andReturn('1200');
            $this->inputInterface
                ->allows('getOption')
                ->with('date')
                ->andReturnNull();
            $target = Carbon::today()->startOfDay()->hour(12)->minute(0)->subMinutes(120);
            $this->createCallingUseCase
                ->allows('handle')
                ->with(
                    CarbonRange::create(['start' => $target->subMinutes(5), 'end' => $target]),
                    $this->examples->organizations[0]->id,
                );

            $this->assertSame(
                CreateCallingCommand::SUCCESS,
                $this->command->handle(
                    $this->organizationIterator,
                    $this->createCallingUseCase,
                ),
            );
        });
    }
}
