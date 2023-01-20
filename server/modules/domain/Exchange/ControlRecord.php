<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

/**
 * コントロールレコード.
 */
abstract class ControlRecord extends ExchangeRecord
{
    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [self::RECORD_TYPE_CONTROL];
    }
}
