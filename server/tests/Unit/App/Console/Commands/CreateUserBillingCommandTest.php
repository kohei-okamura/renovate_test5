<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console\Commands;

use App\Console\Commands\CreateCallingCommand;
use App\Console\Commands\CreateUserBillingCommand;
use App\Console\ConsoleContext;
use Closure;
use Domain\Common\Carbon;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConsoleInputInterfaceMixin;
use Tests\Unit\Mixins\CreateUserBillingListUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationIteratorMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Console\Commands\CreateUserBillingCommand} のテスト.
 */
final class CreateUserBillingCommandTest extends Test
{
    use CarbonMixin;
    use ConsoleInputInterfaceMixin;
    use CreateUserBillingListUseCaseMixin;
    use ExamplesConsumer;
    use OrganizationIteratorMixin;
    use LoggerMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private ConsoleContext $context;
    private CreateUserBillingCommand $command;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->context = new ConsoleContext($self->examples->organizations[0]);

            $self->organizationIterator
                ->allows('iterate')
                ->andReturnUsing(function (Closure $f) use ($self): void {
                    $f($self->context);
                })
                ->byDefault();
            $self->createUserBillingListUseCase
                ->allows('handle')
                ->with($self->context, anInstanceOf(Carbon::class))
                ->andReturnNull()
                ->byDefault();

            $self->command = app(CreateUserBillingCommand::class);
            $self->command->setInput($self->inputInterface);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return failure if not specify `providedIn` option when `batch` not specify', function (): void {
            $this->inputInterface
                ->expects('getOption')
                ->with('batch')
                ->andReturn(false);
            $this->inputInterface
                ->expects('getOption')
                ->with('providedIn')
                ->andReturnNull();
            $this->logger
                ->expects('error')
                ->andReturnNull();

            $this->assertSame(
                CreateUserBillingCommand::FAILURE,
                app()->call([$this->command, 'handle'])
            );
        });
        $this->should('run normally with `batch` option', function (): void {
            $this->inputInterface
                ->expects('getOption')
                ->with('batch')
                ->andReturn(true);
            $this->createUserBillingListUseCase
                ->expects('handle')
                ->with($this->context, equalTo(Carbon::now()->subMonth()->firstOfMonth()))
                ->andReturnNull();

            $this->assertSame(
                CreateUserBillingCommand::SUCCESS,
                app()->call([$this->command, 'handle'])
            );
        });
        $this->should('run normally with `providedIn` option', function (): void {
            $this->inputInterface
                ->expects('getOption')
                ->with('batch')
                ->andReturn(false);
            $this->inputInterface
                ->expects('getOption')
                ->with('providedIn')
                ->andReturn('2020-10');
            $this->createUserBillingListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Carbon::parse('2020-10')->firstOfMonth()),
                )
                ->andReturnNull();

            $this->assertSame(
                CreateUserBillingCommand::SUCCESS,
                app()->call([$this->command, 'handle'])
            );
        });

        $this->should('return failure when `providedIn` option is invalid', function (): void {
            $this->inputInterface
                ->expects('getOption')
                ->with('batch')
                ->andReturn(false);
            $this->inputInterface
                ->expects('getOption')
                ->with('providedIn')
                ->andReturn('20201');
            $this->createUserBillingListUseCase
                ->expects('handle')
                ->never();
            $this->logger
                ->expects('error')
                ->andReturnNull();

            $this->assertSame(
                CreateCallingCommand::FAILURE,
                app()->call([$this->command, 'handle'])
            );
        });
    }
}
