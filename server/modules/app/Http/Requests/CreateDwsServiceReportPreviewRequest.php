<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * サービス提供票実績記録票（プレビュー版）作成リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 */
class CreateDwsServiceReportPreviewRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * サービス提供票実績記録票（プレビュー版）作成用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'officeId' => $this->officeId,
            'userId' => $this->userId,
            'providedIn' => Carbon::parse($this->providedIn),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::updateDwsProvisionReports()],
            'userId' => ['required', 'user_exists:' . Permission::updateDwsProvisionReports()],
            'providedIn' => ['required', 'date_format:Y-m'],
        ];
    }
}
