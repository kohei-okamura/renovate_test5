<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportStatus;

/**
 * 障害福祉サービス：予実状況検索リクエスト.
 */
class FindDwsProvisionReportRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function carbonParams(): array
    {
        return ['providedIn'];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return ['status' => DwsProvisionReportStatus::class];
    }

    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return ['officeId', 'providedIn', 'status', 'q'];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::listDwsProvisionReports()],
            'providedIn' => ['required', 'date_format:Y-m'],
            'status' => ['nullable', 'dws_provision_report_status'],
        ] + parent::rules($input);
    }
}
