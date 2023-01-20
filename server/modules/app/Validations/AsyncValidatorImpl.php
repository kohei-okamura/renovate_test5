<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Domain\Context\Context;
use Domain\Validator\AsyncValidator;
use Lib\Exceptions\ValidationException;
use ScalikePHP\Seq;

/**
 * 非同期処理用バリデータ実装.
 */
abstract class AsyncValidatorImpl implements AsyncValidator
{
    /** {@inheritdoc} */
    public function validate(Context $context, array $data): void
    {
        $validator = CustomValidator::make($context, $data, $this->rules(), [], $this->attributes());
        if ($validator->fails()) {
            throw new ValidationException($this->errorMessage($validator));
        }
    }

    /**
     * バリデーションルールを生成する.
     *
     * @return array
     */
    abstract protected function rules(): array;

    /**
     * エラーメッセージを生成する.
     *
     * @param \App\Validations\CustomValidator $validator
     * @return \ScalikePHP\Seq
     */
    abstract protected function errorMessage(CustomValidator $validator): Seq;

    /**
     * カスタム属性を生成する.
     *
     * @return array
     */
    protected function attributes(): array
    {
        return [];
    }
}
