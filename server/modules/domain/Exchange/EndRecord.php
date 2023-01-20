<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

/**
 * エンドレコード.
 */
final class EndRecord extends ExchangeRecord
{
    private static ?self $instance = null;

    /**
     * インスタンスを返す.
     *
     * @return static
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            self::RECORD_TYPE_END,
            $recordNumber,
        ];
    }
}
