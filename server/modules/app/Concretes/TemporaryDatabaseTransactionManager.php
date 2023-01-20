<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

/**
 * 一時用データベース（MySQL）向けトランザクション管理クラス.
 */
final class TemporaryDatabaseTransactionManager extends DatabaseTransactionManager
{
    private const CONNECTION = 'temporary';

    /**
     * {@link \App\Concretes\TemporaryDatabaseTransactionManager} Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::CONNECTION);
    }
}
