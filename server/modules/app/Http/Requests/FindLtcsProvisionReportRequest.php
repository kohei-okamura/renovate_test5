<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportStatus;

/**
 * 介護保険サービス：予実状況検索リクエスト.
 */
class FindLtcsProvisionReportRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function carbonParams(): array
    {
        return ['providedIn'];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return ['status' => LtcsProvisionReportStatus::class];
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
            'officeId' => ['required', 'office_exists:' . Permission::listLtcsProvisionReports()],
            'providedIn' => ['required', 'date_format:Y-m'],
            'status' => ['nullable', 'ltcs_provision_report_status'],
        ] + parent::rules($input);
    }
}
