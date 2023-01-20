<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 障害福祉サービス：請求生成リクエスト.
 *
 * @property-read int $officeId 事業所ID
 * @property-read string $transactedIn 処理対象年月
 */
class CreateDwsBillingRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 障害福祉サービス：請求生成用データを返す.
     *
     * @return array
     */
    public function payload(): array
    {
        $officeId = $this->officeId;
        [$year, $month] = explode('-', $this->transactedIn, 2);
        $transactedIn = Carbon::create((int)$year, (int)$month);
        $fixedAt = CarbonRange::create([
            'start' => $transactedIn->subMonth()->day(11),
            'end' => $transactedIn->day(10)->endOfDay(),
        ]);

        return compact('officeId', 'transactedIn', 'fixedAt');
    }

    /** {@inheritdoc} */
    protected function messages(): array
    {
        return [
            'officeId.dws_provision_report_exists' => '対象となる予実が存在しません。',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::createBillings(), 'dws_provision_report_exists:transactedIn'],
            'transactedIn' => ['required', 'date_format:Y-m'],
        ];
    }
}
