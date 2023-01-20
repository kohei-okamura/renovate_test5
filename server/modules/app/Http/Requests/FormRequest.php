<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Response\JsonResponse;
use App\Validations\CustomValidator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidatesWhenResolvedTrait;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

/**
 * Form Request Support.
 */
trait FormRequest
{
    use ValidatesWhenResolvedTrait;

    /**
     * Input values.
     */
    protected ?array $values = null;

    /**
     * Create validator instance.
     *
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function createValidatorInstance(array $input): Validator
    {
        $validator = CustomValidator::make(
            $this->context(),
            $input,
            $this->rules($input),
            $this->messages(),
            $this->attributes()
        );
        // https://github.com/laravel/framework/blob/v8.22.1/src/Illuminate/Foundation/Http/FormRequest.php#L87L89
        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }
        return $validator;
    }

    /**
     * Validation attributes.
     *
     * @return array
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore 単体テストでは createValidatorInstance() を呼び出してテストするため
     */
    protected function failedValidation(Validator $validator): void
    {
        $data = ['errors' => $validator->errors()];
        throw new ValidationException($validator, JsonResponse::badRequest($data));
    }

    /** {@inheritdoc} */
    protected function getValidatorInstance(): Validator
    {
        $route = $this->route();
        $params = $route[2] ?? [];
        return $this->createValidatorInstance($params + $this->input() + $this->file());
    }

    /**
     * Validation messages.
     *
     * @return array
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Validation rules.
     *
     * @param array $input
     * @return array
     */
    abstract protected function rules(array $input): array;

    /**
     * Get all input values.
     */
    protected function values()
    {
        if ($this->values === null) {
            $this->values = $this->all();
        }
        return $this->values;
    }

    /** {@inheritdoc} */
    public function __get($key)
    {
        return Arr::get($this->values(), $key, fn () => $this->route($key));
    }

    /** {@inheritdoc} */
    public function __isset($key)
    {
        $values = $this->values();
        return isset($values[$key]);
    }
}
