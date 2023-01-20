<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console\Commands;

use App\Console\Commands\BulkCreateAttendanceCommand;
use Domain\Common\Carbon;
use Illuminate\Console\Command;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BulkCreateAttendanceUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConsoleInputInterfaceMixin;
use Tests\Unit\Mixins\GetAllValidOrganizationUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOrganizationByCodeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Console\Commands\BulkCreateAttendanceCommand} のテスト.
 */
class BulkCreateAttendanceCommandTest extends Test
{
    use BulkCreateAttendanceUseCaseMixin;
    use CarbonMixin;
    use ConsoleInputInterfaceMixin;
    use ExamplesConsumer;
    use GetAllValidOrganizationUseCaseMixin;
    use LoggerMixin;
    use LookupOrganizationByCodeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private BulkCreateAttendanceCommand $command;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BulkCreateAttendanceCommandTest $self): void {
            $self->inputInterface
                ->allows('getOption')
                ->with('daily')
                ->andReturn(true)
                ->byDefault();
            $self->inputInterface
                ->allows('getOption')
                ->with('targetDate')
                ->andReturn(null)
                ->byDefault();
            $self->inputInterface
                ->allows('getOption')
                ->with('organization')
                ->andReturn(null)
                ->byDefault();
            $self->inputInterface
                ->allows('getArguments')
                ->andReturn([])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();
            $self->bulkCreateAttendanceUseCase
                ->allows('handle')
                ->andReturn(1)
                ->byDefault();
            $self->lookupOrganizationByCodeUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->organizations[0]))
                ->byDefault();
            $self->getAllValidOrganizationUseCase
                ->allows('handle')
                ->andReturn(Seq::fromArray($self->examples->organizations))
                ->byDefault();

            $self->command = app(BulkCreateAttendanceCommand::class);
            $self->command->setInput($self->inputInterface);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_handle(): void
    {
        $this->should('return error if not specify `targetDate` and `organization` options when `daily` not specify', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('daily')
                ->andReturn(false)
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('targetDate')
                ->andReturn(null)
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('organization')
                ->andReturn(null)
                ->byDefault();

            $this->assertSame(
                1,
                $this->command->handle(
                    $this->lookupOrganizationByCodeUseCase,
                    $this->getAllValidOrganizationUseCase,
                    $this->bulkCreateAttendanceUseCase
                )
            );
        });

        $this->should('run normally with daily option', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('daily')
                ->andReturn(true)
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('targetDate')
                ->andReturn(null)
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('organization')
                ->andReturn(null)
                ->byDefault();

            $this->assertSame(
                0,
                $this->command->handle(
                    $this->lookupOrganizationByCodeUseCase,
                    $this->getAllValidOrganizationUseCase,
                    $this->bulkCreateAttendanceUseCase
                )
            );
        });

        $this->should('run normally with `targetDate` and `organization` options', function (): void {
            $this->inputInterface
                ->allows('getOption')
                ->with('daily')
                ->andReturn(false)
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('targetDate')
                ->andReturn(Carbon::now()->toDateString())
                ->byDefault();
            $this->inputInterface
                ->allows('getOption')
                ->with('organization')
                ->andReturn($this->examples->organizations[0]->code)
                ->byDefault();

            $this->assertSame(
                0,
                $this->command->handle(
                    $this->lookupOrganizationByCodeUseCase,
                    $this->getAllValidOrganizationUseCase,
                    $this->bulkCreateAttendanceUseCase
                )
            );
        });

        $this->should('use UseCase the process of the command', function (): void {
            $this->bulkCreateAttendanceUseCase
                ->expects('handle')
                ->withArgs(function (Carbon $carbon, int ...$id): bool {
                    return $id[0] === $this->examples->organizations[0]->id
                        && $carbon->equalTo(Carbon::yesterday()->startOfDay());
                })
                ->andReturn(1);

            $this->assertSame(
                0,
                $this->command->handle(
                    $this->lookupOrganizationByCodeUseCase,
                    $this->getAllValidOrganizationUseCase,
                    $this->bulkCreateAttendanceUseCase
                )
            );
        });

        $this->should('return error when organization code invalid.', function (): void {
            $organizationCode = 'INVALID';
            $this->inputInterface
                ->allows('getOption')
                ->with('organization')
                ->andReturn($organizationCode)
                ->byDefault();
            $this->lookupOrganizationByCodeUseCase
                ->expects('handle')
                ->with($organizationCode)
                ->andReturn(Option::none());
            $this->logger
                ->expects('error')
                ->andReturnNull();

            $this->assertSame(
                Command::FAILURE,
                $this->command->handle(
                    $this->lookupOrganizationByCodeUseCase,
                    $this->getAllValidOrganizationUseCase,
                    $this->bulkCreateAttendanceUseCase
                )
            );
        });
    }
}
