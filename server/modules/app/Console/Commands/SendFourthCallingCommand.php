<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\OrganizationIterator;
use App\Jobs\SendFourthCallingJob;
use Domain\Calling\Calling;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\QueueingDispatcher;

/**
 * 出勤確認第四通知コマンド.
 */
class SendFourthCallingCommand extends Command
{
    use BatchDateTimeCommandOptionSupport;

    /**
     * コンソールコマンドの名前と引数、オプション.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        calling:fourth-notify
            {--batch : バッチ処理(現在時刻で処理する)}
            {--date= : 処理日付(YYYYMMDD). batch指定時は無視. デフォルトは当日}
            {--time= : 処理時刻(HHii). batch未指定時に必須.}
    SIGNATURE;

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '出勤確認第四通知';

    /**
     * 出勤確認通知コマンド処理.
     *
     * @param \App\Console\OrganizationIterator $iterator
     * @param \Illuminate\Contracts\Bus\QueueingDispatcher $dispatcher
     * @return int
     */
    public function handle(OrganizationIterator $iterator, QueueingDispatcher $dispatcher): int
    {
        try {
            $range = $this->getRange();
            $iterator->iterate(function (Context $context) use ($dispatcher, $range): void {
                $dispatcher->dispatch(new SendFourthCallingJob($context, $range));
            });
            return Command::SUCCESS;
        } catch (Exception $e) {
            return Command::FAILURE;
        }
    }

    /**
     * 対象日時の範囲を取得する.
     *
     * @return \Domain\Common\CarbonRange
     */
    private function getRange(): CarbonRange
    {
        $target = $this->getTargetDatetime();
        $start = $target->addMinutes(Calling::FOURTH_TARGET_MINUTES);
        return CarbonRange::create([
            'start' => $start,
            'end' => $start->addMinutes(Calling::TARGET_RANGE),
        ]);
    }
}
