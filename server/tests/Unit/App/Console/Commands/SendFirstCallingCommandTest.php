<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console\Commands;

use App\Console\Commands\SendFirstCallingCommand;
use App\Console\ConsoleContext;
use App\Jobs\SendFirstCallingJob;
use Closure;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConsoleInputInterfaceMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\OrganizationIteratorMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Console\Commands\SendFirstCallingCommand} Test.
 */
class SendFirstCallingCommandTest extends Test
{
    use CarbonMixin;
    use ConsoleInputInterfaceMixin;
    use ExamplesConsumer;
    use JobsDispatcherMixin;
    use OrganizationIteratorMixin;
    use UnitSupport;

    private ConsoleContext $context;
    private SendFirstCallingCommand $command;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendFirstCallingCommandTest $self): void {
            $self->context = new ConsoleContext($self->examples->organizations[0]);

            $self->organizationIterator
                ->allows('iterate')
                ->andReturnUsing(function (Closure $f) use ($self): void {
                    $f($self->context);
                })
                ->byDefault();

            $self->command = app(SendFirstCallingCommand::class);
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

            $this->assertSame(
                SendFirstCallingCommand::FAILURE,
                $this->command->handle(
                    $this->organizationIterator,
                    $this->dispatcher
                ),
            );
        });
        $this->should('return error if `time` option format error when `batch` not specify', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('batch')
                ->andReturn(false)
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('time')
                ->andReturn('ABCD1234EFGH');

            $this->assertSame(
                SendFirstCallingCommand::FAILURE,
                $this->command->handle($this->organizationIterator, $this->dispatcher),
            );
        });
        $this->should('run normally with `batch` option', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('batch')
                ->andReturn(true);

            $this->assertSame(
                SendFirstCallingCommand::SUCCESS,
                $this->command->handle($this->organizationIterator, $this->dispatcher),
            );

            $this->dispatcher->assertDispatched(SendFirstCallingJob::class);
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

            $this->assertSame(
                SendFirstCallingCommand::SUCCESS,
                $this->command->handle($this->organizationIterator, $this->dispatcher),
            );

            $this->dispatcher->assertDispatched(SendFirstCallingJob::class);
        });
    }
}
