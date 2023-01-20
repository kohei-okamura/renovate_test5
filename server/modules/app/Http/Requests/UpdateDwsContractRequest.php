<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsServiceDivisionCode;
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
use Lib\Arrays;
use UseCase\Contract\GetOverlapContractUseCase;

/**
 * 障害福祉サービス契約更新リクエスト.
 *
 * @property-read int $userId 利用者ID (URLパラメータから)
 * @property-read int $officeId
 * @property-read int $status
 * @property-read null|string $contractedOn
 * @property-read null|string $terminatedOn
 * @property-read array $dwsPeriods
 * @property-read null|string $note
 */
class UpdateDwsContractRequest extends StaffRequest implements ValidatesWhenResolved
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
                'status' => $status,
                'contractedOn' => Carbon::parseOption($this->contractedOn)->orNull(),
                'terminatedOn' => Carbon::parseOption($this->terminatedOn)->orNull(),
                'dwsPeriods' => Arrays::generate(function (): iterable {
                    foreach (DwsServiceDivisionCode::all() as $code) {
                        $codeValue = $code->value();
                        $value = ContractPeriod::create([
                            'start' => Carbon::parseOption(Arr::get($this->dwsPeriods, "{$codeValue}.start"))->orNull(),
                            'end' => Carbon::parseOption(Arr::get($this->dwsPeriods, "{$codeValue}.end"))->orNull(),
                        ]);
                        yield $codeValue => $value;
                    }
                }),
                'ltcsPeriod' => ContractPeriod::create(['start' => null, 'end' => null]),
                'expiredReason' => LtcsExpiredReason::unspecified(),
                'note' => $this->note ?? '',
            ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        // `dwsPeriods.*.start` 表記では反映されない。ルールにワイルドカードを用いていないから……？
        $homeHelpService = DwsServiceDivisionCode::homeHelpService()->value();
        $visitingCareForPwsd = DwsServiceDivisionCode::visitingCareForPwsd()->value();
        return [
            'contractedOn' => '契約日',
            "dwsPeriods.{$homeHelpService}.start" => '初回サービス提供日',
            "dwsPeriods.{$visitingCareForPwsd}.start" => '初回サービス提供日',
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
        $homeHelpService = DwsServiceDivisionCode::homeHelpService()->value();
        $visitingCareForPwsd = DwsServiceDivisionCode::visitingCareForPwsd()->value();
        $statusValue = Arr::get($input, 'status');
        $status = ContractStatus::isValid($statusValue) ? ContractStatus::from($statusValue) : null;
        return [
            'officeId' => [
                Rule::requiredIf(fn (): bool => $status !== ContractStatus::disabled()),
                'nullable',
                'office_exists:' . Permission::updateDwsContracts(),
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
                    'dws_contract_can_be_terminated:userId,officeId,' . Permission::updateDwsContracts()
                ),
            ],
            "dwsPeriods.{$homeHelpService}.start" => [
                'bail',
                Rule::requiredIf(function () use ($input, $status, $visitingCareForPwsd): bool {
                    return $status !== ContractStatus::disabled()
                        && empty($input['dwsPeriods'][$visitingCareForPwsd]['start']);
                }),
                'date',
                'after_or_equal:contractedOn',
            ],
            "dwsPeriods.{$homeHelpService}.end" => [
                'bail',
                Rule::requiredIf(function () use ($input, $status, $homeHelpService): bool {
                    return $status === ContractStatus::terminated()
                        && !empty($input['dwsPeriods'][$homeHelpService]['start']);
                }),
                'date',
                "after:dwsPeriods.{$homeHelpService}.start",
            ],
            "dwsPeriods.{$visitingCareForPwsd}.start" => [
                'bail',
                Rule::requiredIf(function () use ($input, $status, $homeHelpService): bool {
                    return $status !== ContractStatus::disabled()
                        && empty($input['dwsPeriods'][$homeHelpService]['start']);
                }),
                'date',
                'after_or_equal:contractedOn',
            ],
            "dwsPeriods.{$visitingCareForPwsd}.end" => [
                'bail',
                Rule::requiredIf(function () use ($input, $status, $visitingCareForPwsd): bool {
                    return $status === ContractStatus::terminated()
                        && !empty($input['dwsPeriods'][$visitingCareForPwsd]['start']);
                }),
                'date',
                "after:dwsPeriods.{$visitingCareForPwsd}.start",
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
            // 現状重複した仮契約が作れないためこのバリデーションはいらないかもしれない
            $data = $validator->getData();
            if (isset($data['status']) && $data['status'] === ContractStatus::formal()->value()) {
                /** @var \UseCase\Contract\GetOverlapContractUseCase $useCase */
                $useCase = app(GetOverlapContractUseCase::class);
                $isOverlapped = $useCase
                    ->handle(
                        $this->context(),
                        Permission::updateDwsContracts(),
                        +$data['userId'],
                        +$data['officeId'],
                        ServiceSegment::disabilitiesWelfare(),
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
