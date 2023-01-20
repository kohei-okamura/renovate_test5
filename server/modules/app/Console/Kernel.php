<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\BulkCreateAttendanceCommand;
use App\Console\Commands\CreateCallingCommand;
use App\Console\Commands\CreateStaffCommand;
use App\Console\Commands\CreateUserBillingCommand;
use App\Console\Commands\ImportDwsHomeHelpServiceDictionaryCommand;
use App\Console\Commands\ImportDwsVisitingCareForPwsdDictionaryCommand;
use App\Console\Commands\ImportLtcsHomeVisitLongTermCareDictionaryCommand;
use App\Console\Commands\ImportOfficeCommand;
use App\Console\Commands\ImportUserCommand;
use App\Console\Commands\SendFirstCallingCommand;
use App\Console\Commands\SendFourthCallingCommand;
use App\Console\Commands\SendSecondCallingCommand;
use App\Console\Commands\SendThirdCallingCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Application;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Kernel for ConsoleCommand.
 *
 * @codeCoverageIgnore artisanコマンドの機能となるためUnitTest除外
 */
final class Kernel extends ConsoleKernel
{
    /**
     * {@link \App\Console\Kernel} constructor.
     *
     * @param \Laravel\Lumen\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->commands = $this->commands();
    }

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // MEMO 以下はサンプルで、実際に動作させるかは別途検証が必要です。
        $schedule
            ->command('staff-attendance:create --batch')
            ->everyFiveMinutes()
            ->after(function (): void {
//                $schedule->command('staff-attendance:third --batch')->runInBackground();
//                $schedule->command('staff-attendance:second --batch')->runInBackground();
//                $schedule->command('staff-attendance:first --batch')->runInBackground();
            });
    }

    /**
     * Artisan コマンドの一覧を返す.
     *
     * @return array
     */
    private function commands(): array
    {
        return $this->app->environment() === 'local'
            ? [...$this->commandsForAll(), ...$this->commandsForLocal()]
            : $this->commandsForAll();
    }

    /**
     * 全環境で利用可能な Artisan コマンドの一覧を返す.
     *
     * @return array
     */
    private function commandsForAll(): array
    {
        return [
            BulkCreateAttendanceCommand::class,
            CreateCallingCommand::class,
            CreateStaffCommand::class,
            CreateUserBillingCommand::class,
            ImportDwsHomeHelpServiceDictionaryCommand::class,
            ImportDwsVisitingCareForPwsdDictionaryCommand::class,
            ImportLtcsHomeVisitLongTermCareDictionaryCommand::class,
            ImportOfficeCommand::class,
            ImportUserCommand::class,
            SendFirstCallingCommand::class,
            SendSecondCallingCommand::class,
            SendThirdCallingCommand::class,
            SendFourthCallingCommand::class,
        ];
    }

    /**
     * local 環境でのみ利用可能な Artisan コマンドの一覧を返す.
     *
     * @return array
     */
    private function commandsForLocal(): array
    {
        return [
            // TODO: DEV-3092 Horizonを追加・設定する
            // VendorPublishCommand::class,
        ];
    }
}
