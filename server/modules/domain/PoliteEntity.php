<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

/**
 * PHP 8.1 版エンティティ基底クラス.
 */
abstract class PoliteEntity extends Polite
{
    /**
     * {@link \Domain\PoliteEntity} constructor.
     *
     * @param null|int $id
     */
    public function __construct(
        public readonly ?int $id
    ) {
    }
}
