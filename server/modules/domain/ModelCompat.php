<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

/**
 * ドメインモデル互換インターフェース.
 */
interface ModelCompat
{
    /**
     * Get an attr from the model instance.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * 連想配列形式のデータを返す.
     *
     * @return array
     */
    public function toAssoc(): array;
}
