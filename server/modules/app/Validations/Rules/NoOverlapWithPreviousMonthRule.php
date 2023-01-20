<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use DateTimeInterface;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 前月の予実の最終日と重複していないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoOverlapWithPreviousMonthRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateNoOverlapWithPreviousMonth(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(4, $parameters, 'no_overlap_with_previous_month');

        // 配列でない場合にはこのバリデーションではエラーとしない
        if (!is_array($value)) {
            return true;
        }

        // スケジュール
        $schedules = Seq::fromArray($value)
            ->filter(
                fn (array $x): bool => DwsProjectServiceCategory::isValid($x['category'])
                    && DwsProjectServiceCategory::from($x['category']) !== DwsProjectServiceCategory::ownExpense()
            )
            ->map(fn (array $x): array => $x['schedule'])
            ->filter(
                fn (array $x): bool => $this->validateDateFormat('', $x['date'], ['Y-m-d'])
                    && $this->validateDateFormat('', $x['start'], [DateTimeInterface::ISO8601])
                    && $this->validateDateFormat('', $x['end'], [DateTimeInterface::ISO8601])
            )
            ->filter(fn (array $x): bool => Carbon::parse($x['date'])->day === 1)
            ->map(fn (array $x): Schedule => Schedule::create([
                'date' => Carbon::parse($x['date']),
                'start' => Carbon::parse($x['start']),
                'end' => Carbon::parse($x['end']),
            ]))
            ->computed();

        // 月初めに自費サービス以外の予実が存在しない場合はチェックしない
        if ($schedules->isEmpty()) {
            return true;
        }

        $officeId = Arr::get($this->data, $parameters[0]);
        $userId = Arr::get($this->data, $parameters[1]);
        $providedInString = Arr::get($this->data, $parameters[2]);
        $plansOrResults = $parameters[3];
        assert($plansOrResults === 'plans' || $plansOrResults === 'results');

        // 日付が取得できなかった場合は検証不可能なので、このバリデーションではチェックしない
        if (empty($providedInString)) {
            return true;
        }

        $providedIn = Carbon::parse($providedInString);

        $filterParams = [
            'officeId' => $officeId,
            'userId' => $userId,
            'providedIn' => $providedIn->subMonth(),
            'status' => DwsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportFinder $provisionReportFinder */
        $provisionReportFinder = app(DwsProvisionReportFinder::class);

        return $provisionReportFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->headOption()
            ->map(function (DwsProvisionReport $x) use ($plansOrResults, $schedules): bool {
                $previousSchedules = Seq::fromArray($x->get($plansOrResults))
                    ->filter(fn (DwsProvisionReportItem $x): bool => $x->category !== DwsProjectServiceCategory::ownExpense())
                    ->filter(fn (DwsProvisionReportItem $x): bool => $x->schedule->date->isLastOfMonth())
                    ->map(fn (DwsProvisionReportItem $x): Schedule => $x->schedule);
                // 前月予実の最終日と当月予実の初日が重複していないことを検証
                return $previousSchedules->forAll(
                    fn (Schedule $y): bool => $schedules->forAll(
                        fn (Schedule $z): bool => !$z->toRange()->isOverlapping($y->toRange(), false)
                    )
                );
            })
            ->getOrElseValue(true);
    }
}
