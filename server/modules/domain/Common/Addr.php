<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Polite;

/**
 * 住所.
 */
final class Addr extends Polite
{
    /**
     * {@link \Domain\Common\Addr} constructor.
     *
     * @param string $postcode 郵便番号
     * @param \Domain\Common\Prefecture $prefecture 都道府県
     * @param string $city 市区町村
     * @param string $street 町名・番地
     * @param string $apartment 建物名など
     */
    public function __construct(
        public readonly string $postcode,
        public readonly Prefecture $prefecture,
        public readonly string $city,
        public readonly string $street,
        public readonly string $apartment
    ) {
    }
}
