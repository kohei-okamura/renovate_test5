<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Lib\Math;

/**
 * 自費サービス情報作成リクエスト.
 *
 * @property-read null|int $officeId
 * @property-read string $name
 * @property-read int $durationMinutes
 * @property-read array $fee
 * @property-read string $note
 */
class CreateOwnExpenseProgramRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    // TODO DEV-6109
    public const CONSUMPTION_TAX_RATE = 10;
    public const REDUCE_CONSUMPTION_TAX_RATE = 8;

    /**
     * 自費サービス情報を生成する.
     *
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram
     */
    public function payload(): OwnExpenseProgram
    {
        return OwnExpenseProgram::create([
            'officeId' => $this->officeId ?? null,
            'name' => $this->name,
            'durationMinutes' => $this->durationMinutes,
            'fee' => Expense::create([
                'taxExcluded' => $this->fee['taxExcluded'],
                'taxIncluded' => $this->fee['taxIncluded'],
                'taxType' => TaxType::from($this->fee['taxType']),
                'taxCategory' => TaxCategory::from($this->fee['taxCategory']),
            ]),
            'note' => $this->note ?? '',
        ]);
    }

    /**
     * バリデータインスタンスの設定.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->failed()) {
                return;
            }
            $data = $validator->getData();
            $taxCategory = Arr::get($data, 'fee.taxCategory');
            if ($taxCategory === TaxCategory::unapplicable()->value()) {
                // 税率区分なしは検証しない
                return;
            }

            $rate = $taxCategory === TaxCategory::consumptionTax()->value()
                ? self::CONSUMPTION_TAX_RATE
                : self::REDUCE_CONSUMPTION_TAX_RATE;

            $taxType = Arr::get($data, 'fee.taxType');
            if ($taxType === TaxType::taxExcluded()->value()) {
                $this->validateTaxInclude($data['fee'], $rate, $validator);
            } elseif ($taxType === TaxType::taxIncluded()->value()) {
                $this->validateTaxExclude($data['fee'], $rate, $validator);
            } else {
                $this->validateTaxExempted($data['fee'], $rate, $validator);
            }
        });
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['nullable', 'office_exists:' . Permission::createOwnExpensePrograms()],
            'name' => ['required', 'string', 'max:200'],
            'durationMinutes' => ['required', 'integer'],
            'fee' => ['required', 'array'],
            'fee.taxExcluded' => ['required', 'integer'],
            'fee.taxIncluded' => ['required', 'integer'],
            'fee.taxType' => ['required', 'tax_type'],
            'fee.taxCategory' => ['required', 'tax_category'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * 税込みをバリデーションする.
     *
     * @param array $fee
     * @param int $rate 税率
     * @param \Illuminate\Validation\Validator $validator
     */
    private function validateTaxInclude(array $fee, int $rate, Validator $validator): void
    {
        $expectedInclude = Math::floor($fee['taxExcluded'] * ($rate + 100) / 100);
        if ($expectedInclude !== $fee['taxIncluded']) {
            $validator->errors()->add('fee.taxIncluded', '税込金額が正しくありません。');
        }
    }

    /**
     * 税抜をバリデーションする.
     *
     * @param array $fee
     * @param int $rate 税率
     * @param \Illuminate\Validation\Validator $validator
     */
    private function validateTaxExclude(array $fee, int $rate, Validator $validator): void
    {
        $expectedExclude = Math::ceil($fee['taxIncluded'] * 100 / ($rate + 100));
        if ($expectedExclude !== $fee['taxExcluded']) {
            $validator->errors()->add('fee.taxExcluded', '税抜金額が正しくありません。');
        }
    }

    /**
     * 非課税をバリデーションする.
     *
     * @param array $fee
     * @param int $rate 税率
     * @param \Illuminate\Validation\Validator $validator
     */
    private function validateTaxExempted(array $fee, int $rate, Validator $validator): void
    {
        if ($fee['taxIncluded'] !== $fee['taxExcluded']) {
            $validator->errors()->add('fee.taxExcluded', '税抜金額が正しくありません。');
            $validator->errors()->add('fee.taxIncluded', '税込金額が正しくありません。');
        }
    }
}
