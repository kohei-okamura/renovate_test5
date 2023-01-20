<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Common\Carbon;
use Domain\Organization\Organization;
use Illuminate\Console\Command;
use Lib\Logging;
use ScalikePHP\Option;
use UseCase\Organization\GetAllValidOrganizationUseCase;
use UseCase\Organization\LookupOrganizationByCodeUseCase;
use UseCase\Shift\BulkCreateAttendanceUseCase;

/**
 * 勤務実績一括登録コマンド.
 */
class BulkCreateAttendanceCommand extends Command
{
    use Logging;

    /**
     * コンソールコマンドの名前と引数、オプション.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        attendance:create-from-shifts
            {--daily : 日時処理(全事業者分/前日分 を取り込む)}
            {--targetDate= : 処理する日付(YYYY-MM-DD). daily未指定時は必須. daily指定時のデフォルトは前日}
            {--organization= : 事業者コード. daily未指定時は必須. daily指定時のとき無指定は全ての事業者.}
    SIGNATURE;

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '勤務実績一括登録';

    /**
     * コンソールコマンドの実行.
     *
     * @param \UseCase\Organization\LookupOrganizationByCodeUseCase $lookupOrganizationByCodeUseCase
     * @param \UseCase\Organization\GetAllValidOrganizationUseCase $getAllValidOrganizationUseCase
     * @param \UseCase\Shift\BulkCreateAttendanceUseCase $useCase
     * @return int 0: succeed, otherwise error
     */
    public function handle(
        LookupOrganizationByCodeUseCase $lookupOrganizationByCodeUseCase,
        GetAllValidOrganizationUseCase $getAllValidOrganizationUseCase,
        BulkCreateAttendanceUseCase $useCase
    ): int {
        $this->logger()->info(
            '勤務実績一括登録コマンドを実行します',
            [
                'command' => 'attendance:create-from-shifts',
                'arguments' => $this->arguments(),
            ]
        );
        $dailyFlag = $this->option('daily');
        $targetDateInput = $this->option('targetDate');
        $organizationInput = $this->option('organization');

        if (!$dailyFlag && ($targetDateInput === null || $organizationInput === null)) {
            return 1;
        }

        /** @var string $targetDate */
        $targetDate = $targetDateInput ?? Carbon::yesterday()->toDateString();
        $organizationCodeOption = Option::from($organizationInput);
        $organizations = $organizationCodeOption->isEmpty()
            ? $getAllValidOrganizationUseCase->handle()
            : $lookupOrganizationByCodeUseCase->handle($organizationCodeOption->get())->toSeq();
        if ($organizations->isEmpty()) {
            $this->logger()->error("Organization[{$organizationInput}] not found");
            return 1;
        }
        $organizationIds = $organizations->map(fn (Organization $x): int => $x->id);

        $count = $useCase->handle(Carbon::parse($targetDate), ...$organizationIds);

        $this->logger()->info(
            '勤務実績一括登録コマンドを実行しました',
            [
                'command' => 'attendance:create-from-shifts',
                'arguments' => $this->arguments(),
                'count' => $count,
            ]
        );
        return 0;
    }
}
