<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス利用者負担上限額管理結果票登録リクエスト.
 *
 * @property-read int $dwsBillingBundleId
 * @property-read int $userId
 * @property-read int $exchangeAim
 * @property-read int $result
 * @property-read array $items
 */
class CreateDwsBillingCopayCoordinationRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 利用者負担上限額管理結果票 生成Assocを組み立てる.
     *
     * sample:
     *  [
     *      'userId' => 5,
     *      'result' => CopayCoordinationResult::from(...),
     *      'items' => [
     *          [
     *              'itemNumber' => 1,
     *              'officeId' => 1234,
     *              'subtotal' => DwsBillingCopayCoordinationPayment::create([...]),
     *          ],
     *          [
     *              'itemNumber' => 2,
     *              'officeId' => 5678,
     *              'subtotal' => DwsBillingCopayCoordinationPayment::create([...]),
     *          ],
     *      ],
     *  ]
     *
     * @return array
     */
    public function payload(): array
    {
        $f = call_user_func(function (): iterable {
            foreach ($this->items as $itemNumber => $item) {
                yield [
                    'itemNumber' => $itemNumber + 1, // 1〜
                    'officeId' => $item['officeId'],
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => $item['subtotal']['fee'],
                        'copay' => $item['subtotal']['copay'],
                        'coordinatedCopay' => $item['subtotal']['coordinatedCopay'],
                    ]),
                ];
            }
        });
        return [
            'userId' => $this->userId,
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::from($this->exchangeAim),
            'result' => CopayCoordinationResult::from($this->result),
            'items' => Seq::fromTraversable($f)->toArray(),
        ];
    }

    /** {@inheritdoc} */
    public function rules(array $input): array
    {
        return [
            'userId' => ['bail', 'required', 'user_exists:' . Permission::createBillings()],
            'exchangeAim' => ['bail', 'required', 'dws_billing_copay_coordination_exchange_aim'],
            'result' => ['bail', 'required', 'copay_coordination_result'],
            'isProvided' => ['required', 'boolean'],
            'items' => [
                'bail',
                'required',
                'array',
                'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                'only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                'items_have_integrity_of_result:result,userId,dwsBillingId,dwsBillingBundleId,' . Permission::createBillings(),
            ],
            'items.*.officeId' => ['bail', 'required', 'office_exists_ignore_permissions', 'office_has_dws_generic_service'],
            'items.*.subtotal' => ['bail', 'required', 'array'],
            'items.*.subtotal.fee' => ['bail', 'required', 'integer'],
            'items.*.subtotal.copay' => ['bail', 'required', 'integer', 'copay_under_copay_limit:userId'],
            'items.*.subtotal.coordinatedCopay' => ['bail', 'required', 'integer'],
        ];
    }
}
