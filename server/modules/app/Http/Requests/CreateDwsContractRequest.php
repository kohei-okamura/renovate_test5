<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Validator;
use UseCase\Contract\GetOverlapContractUseCase;

/**
 * 障害福祉サービス契約作成リクエスト.
 *
 * @property-read int $officeId
 * @property-read null|string $note
 */
class CreateDwsContractRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 契約を生成する.
     *
     * @return \Domain\Contract\Contract
     */
    public function payload(): Contract
    {
        return Contract::create([
            'officeId' => $this->officeId,
            'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
            'status' => ContractStatus::provisional(),
            'contractedOn' => null,
            'terminatedOn' => null,
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([]),
            ],
            'ltcsPeriod' => ContractPeriod::create([]),
            'expiredReason' => LtcsExpiredReason::unspecified(),
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
            // $this ではリクエストパラメータが入ってこないので、validator の値を使って検証
            $data = $validator->getData();
            /** @var \UseCase\Contract\GetOverlapContractUseCase $useCase */
            $useCase = app(GetOverlapContractUseCase::class);
            $isOverlapped = $useCase
                ->handle(
                    $this->context(),
                    Permission::createDwsContracts(),
                    +$data['userId'],
                    +$data['officeId'],
                    ServiceSegment::disabilitiesWelfare()
                )
                ->nonEmpty();
            if ($isOverlapped) {
                $validator->errors()->add('contractedOn', '重複する契約が既に登録されています。ご確認ください。');
            }
        });
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::createDwsContracts()],
            'note' => ['string', 'max:255'],
        ];
    }
}
