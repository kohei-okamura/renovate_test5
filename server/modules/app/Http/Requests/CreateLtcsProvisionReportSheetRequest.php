<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 介護保険サービス：サービス提供票PDF作成リクエスト.
 *
 * @property-read string $issuedOn
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 * @property-read bool $needsMaskingInsNumber
 * @property-read bool $needsMaskingInsName
 */
class CreateLtcsProvisionReportSheetRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 請求書作成用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'officeId' => $this->officeId,
            'userId' => $this->userId,
            'providedIn' => Carbon::parse($this->providedIn),
            'issuedOn' => Carbon::parse($this->issuedOn),
            'needsMaskingInsNumber' => $this->needsMaskingInsNumber ?? false,
            'needsMaskingInsName' => $this->needsMaskingInsName ?? false,
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::updateLtcsProvisionReports()],
            'userId' => ['required', 'user_exists:' . Permission::updateLtcsProvisionReports()],
            'providedIn' => ['required', 'date_format:Y-m'],
            'issuedOn' => ['required', 'date'],
            'needsMaskingInsNumber' => ['nullable', 'boolean_ext'],
            'needsMaskingInsName' => ['nullable', 'boolean_ext'],
        ];
    }
}
