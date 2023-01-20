<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use UseCase\Contract\GetOverlapContractUseCase;

/**
 * 介護保険サービス契約更新リクエスト.
 *
 * @property-read int $organizationId
 * @property-read int $officeId
 * @property-read int $serviceSegment
 * @property-read int $status
 * @property-read null|string $contractedOn
 * @property-read null|string $terminatedOn
 * @property-read array $ltcsPeriod
 * @property-read int $expiredReason
 * @property-read null|string $note
 */
class UpdateLtcsContractRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * リクエストを契約に変換する.
     *
     * @return array
     */
    public function payload(): array
    {
        $status = ContractStatus::from($this->status);
        return $status === ContractStatus::disabled()
            ? ['status' => $status]
            : [
                'officeId' => $this->officeId,
                'status' => ContractStatus::from($this->status),
                'contractedOn' => Carbon::parseOption($this->contractedOn)->orNull(),
                'terminatedOn' => Carbon::parseOption($this->terminatedOn)->orNull(),
                'dwsPeriods' => [],
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::parseOption($this->ltcsPeriod['start'] ?? null)->orNull(),
                    'end' => Carbon::parseOption($this->ltcsPeriod['end'] ?? null)->orNull(),
                ]),
                'expiredReason' => empty($this->expiredReason)
                    ? LtcsExpiredReason::unspecified()
                    : LtcsExpiredReason::from($this->expiredReason),
                'note' => $this->note ?? '',
            ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'contractedOn' => '契約日',
            'ltcsPeriod.start' => '初回サービス提供日',
        ];
    }

    /** {@inheritdoc} */
    protected function messages(): array
    {
        return [
            'after' => ':date以降の日付を入力してください。',
            'after_or_equal' => ':date以降の日付を入力してください。',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $statusValue = Arr::get($input, 'status');
        $status = ContractStatus::isValid($statusValue) ? ContractStatus::from($statusValue) : null;
        return [
            'officeId' => [
                Rule::requiredIf(fn (): bool => $status !== ContractStatus::disabled()),
                'nullable',
                'office_exists:' . Permission::updateLtcsContracts(),
            ],
            'status' => ['required', 'contract_status'],
            'contractedOn' => [
                'bail',
                Rule::requiredIf(fn (): bool => $status !== ContractStatus::disabled()),
                'date',
            ],
            'terminatedOn' => [
                'bail',
                Rule::requiredIf(fn (): bool => $status === ContractStatus::terminated()),
                'date',
                'after:contractedOn',
                Rule::when(
                    $status === ContractStatus::terminated(),
                    'ltcs_contract_can_be_terminated:userId,officeId,' . Permission::updateLtcsContracts()
                ),
            ],
            'ltcsPeriod.start' => [
                'bail',
                Rule::requiredIf(fn (): bool => $status !== ContractStatus::disabled()),
                'date',
                'after_or_equal:contractedOn',
            ],
            'ltcsPeriod.end' => [
                'bail',
                Rule::requiredIf(fn (): bool => $status === ContractStatus::terminated()),
                'date',
                'after:ltcsPeriod.start',
            ],
            'expiredReason' => [
                Rule::requiredIf(fn (): bool => $status === ContractStatus::terminated()),
                'ltcs_expired_reason',
            ],
            'note' => ['string', 'max:255'],
        ];
    }

    /**
     * バリデータインスタンスの設定.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->failed()) {
                return;
            }
            // $this ではリクエストパラメータが入ってこないので、validator の値を使って検証
            $data = $validator->getData();
            if (isset($data['status']) && $data['status'] === ContractStatus::formal()->value()) {
                /** @var \UseCase\Contract\GetOverlapContractUseCase $useCase */
                $useCase = app(GetOverlapContractUseCase::class);
                $isOverlapped = $useCase
                    ->handle(
                        $this->context(),
                        Permission::updateLtcsContracts(),
                        +$data['userId'],
                        +$data['officeId'],
                        ServiceSegment::longTermCare(),
                    )
                    ->filterNot(fn (Contract $x): bool => $x->id === +$data['id'])
                    ->nonEmpty();
                if ($isOverlapped) {
                    $validator->errors()->add('contractedOn', '重複する契約が既に登録されています。ご確認ください。');
                }
            }
        });
    }
}
