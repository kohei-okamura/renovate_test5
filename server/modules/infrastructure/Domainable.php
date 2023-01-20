<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure;

/**
 * ドメインモデルに変換可能なオブジェクト.
 */
interface Domainable
{
    /**
     * Convert to domain model.
     *
     * @return \Domain\Model
     */
    public function toDomain();
}
