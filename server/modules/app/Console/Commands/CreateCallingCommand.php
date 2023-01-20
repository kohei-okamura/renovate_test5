<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\OrganizationIterator;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Exception;
use Illuminate\Console\Command;
use Lib\Logging;
use UseCase\Calling\CreateCallingsUseCase;

/**
 * 出勤確認生成コマンド.
 */
class CreateCallingCommand extends Command
{
    use BatchDateTimeCommandOptionSupport;
    use Logging;

    private const CREATE_CALLING_RANGE_MINUTES = 5; // 通知を生成する時間の幅（分）
    private const CREATE_CALLING_MINUTES = 120; // 何分後に開始する勤務シフトを生成対象にするか

    /**
     * コンソールコマンドの名前と引数、オプション.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        calling:create
            {--batch : バッチ処理(現在時刻で処理する)}
            {--date= : 処理日付(YYYYMMDD). batch指定時は無視. デフォルトは当日}
            {--time= : 処理時刻(HHii). batch未指定時に必須.}
    SIGNATURE;

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '出勤確認通知生成';

    /**
     * コンソールコマンドの実行.
     *
     * @param \App\Console\OrganizationIterator $iterator
     * @param \UseCase\Calling\CreateCallingsUseCase $createCallingsUseCase
     * @return int Status
     */
    public function handle(
        OrganizationIterator $iterator,
        CreateCallingsUseCase $createCallingsUseCase
    ): int {
        try {
            $targetDatetime = $this->getTargetDatetime();
            $end = $targetDatetime->addMinutes(self::CREATE_CALLING_MINUTES);
            $range = CarbonRange::create([
                'start' => $end->subMinutes(self::CREATE_CALLING_RANGE_MINUTES),
                'end' => $end,
            ]);

            $iterator->iterate(function (Context $context) use ($createCallingsUseCase, $range): void {
                $createCallingsUseCase->handle($context, $range);
            });
            return self::SUCCESS;
        } catch (Exception $e) {
            $this->logger()->error($e);
            return self::FAILURE;
        }
    }
}
