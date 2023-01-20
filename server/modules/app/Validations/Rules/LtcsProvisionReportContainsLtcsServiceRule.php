<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportFinder;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 入力事業所の指定した年月に、介護保険サービス：予実の自費以外の実績が存在していることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait LtcsProvisionReportContainsLtcsServiceRule
{
    /**
     * 検証結果
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsProvisionReportContainsLtcsService(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'ltcs_provision_report_contains_ltcs_service');
        $transactedInArg = Arr::get($this->data, $parameters[0]);
        if (strtotime($transactedInArg) === false) {
            return true; // 日付形式エラーではバリデーションしない
        }
        $transactedIn = Carbon::parse($transactedInArg);
        $fixedAt = CarbonRange::create([
            'start' => $transactedIn->subMonth()->day(11)->startOfDay(),
            'end' => $transactedIn->day(10)->endOfDay(),
        ]);
        $officeId = (int)$value;

        /** @var \Domain\ProvisionReport\LtcsProvisionReportFinder $finder */
        $finder = app(LtcsProvisionReportFinder::class);

        $filterParams = [
            'officeId' => $officeId,
            'fixedAt' => $fixedAt,
            'status' => LtcsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];

        return $finder->find($filterParams, $paginationParams)
            ->list
            ->exists(
                fn (LtcsProvisionReport $x) => Seq::from(...$x->entries)
                    ->exists(fn (LtcsProvisionReportEntry $y) => $y->category !== LtcsProjectServiceCategory::ownExpense() && !empty($y->results))
            );
    }
}
