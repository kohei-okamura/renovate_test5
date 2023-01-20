<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Mixins\ContextMixin;

/**
 * RuleTestSupport trait.
 */
trait RuleTestSupport
{
    use ContextMixin;
    use ExamplesConsumer;

    /**
     * CustomValidatorインスタンスを生成する.
     *
     * @param array $data Validateするテストデータ
     * @param array $rule Validateを実行するRule定義
     * @return \App\Validations\CustomValidator
     */
    protected function buildCustomValidator(array $data, array $rule): CustomValidator
    {
        return CustomValidator::make(
            $this->context,
            $data,
            $rule,
            [],
            []
        );
    }
}
