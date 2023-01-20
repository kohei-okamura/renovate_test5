<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Model;
use Domain\Sms\SmsMessage;

/**
 * 出勤確認通知SMSメッセージ.
 *
 * @property-read string $url
 * @property-read \Domain\Shift\Shift $shift
 * @property-read int $minutes 通知時刻（開始分前）
 */
class StaffAttendanceSmsMessage extends Model implements SmsMessage
{
    /**
     * メッセージを取得する.
     *
     * @return string
     */
    public function getMessage(): string
    {
        $date = $this->shift->schedule->date->format('n/j');
        return "【出確{$date}】サービス{$this->minutes}分前!出勤なら即押→ {$this->url} 土屋訪問";
    }

    /**
     * {@inheritdoc}
     */
    protected function attrs(): array
    {
        return [
            'url',
            'shift',
            'minutes',
        ];
    }
}
