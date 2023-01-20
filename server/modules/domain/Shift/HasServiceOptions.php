<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Shift;

use ScalikePHP\Seq;

/**
 * サービスオプションを持つモデル.
 *
 * @property-read \Domain\Shift\ServiceOption[] $options サービスオプション
 */
trait HasServiceOptions
{
    /**
     * 特定のサービスオプションが指定されているかどうかを判定する.
     *
     * @param \Domain\Shift\ServiceOption $option
     * @return bool
     */
    public function hasOption(ServiceOption $option): bool
    {
        return Seq::from(...$this->options)->contains($option);
    }
}
