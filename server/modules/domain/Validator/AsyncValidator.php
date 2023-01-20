<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Validator;

use Domain\Context\Context;

/**
 * 非同期処理用バリデータ.
 */
interface AsyncValidator
{
    /**
     * バリデーションを実行する.
     *
     * @param Context $context
     * @param array $data
     */
    public function validate(Context $context, array $data): void;
}
