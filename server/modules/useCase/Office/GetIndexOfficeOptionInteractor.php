<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Office\OfficeFinder;
use Domain\Office\OfficeOption;
use Domain\Office\Purpose;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 事業所選択肢一覧取得ユースケース実装.
 */
final class GetIndexOfficeOptionInteractor implements GetIndexOfficeOptionUseCase
{
    private FindOfficeUseCase $findOfficeUseCase;
    private OfficeFinder $officeFinder;

    /**
     * {@link \UseCase\Office\GetIndexOfficeOptionInteractor} Constructor.
     *
     * @param \UseCase\Office\FindOfficeUseCase $findOfficeUseCase
     * @param \Domain\Office\OfficeFinder $officeFinder
     */
    public function __construct(FindOfficeUseCase $findOfficeUseCase, OfficeFinder $officeFinder)
    {
        $this->findOfficeUseCase = $findOfficeUseCase;
        $this->officeFinder = $officeFinder;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Option $permissionOption,
        Option $userIdOption,
        Option $purposeOption,
        Option $isCommunityGeneralSupportCenter,
        Seq $qualifications
    ): Seq {
        $filterParams = []
            + $userIdOption->map(fn (int $x): array => ['userId' => $x])->getOrElseValue([])
            + $purposeOption->map(fn (Purpose $x): array => ['purpose' => $x])->getOrElseValue([])
            + $isCommunityGeneralSupportCenter->map(fn (bool $x): array => ['isCommunityGeneralSupportCenter' => $x])->getOrElseValue([])
            + ($qualifications->nonEmpty() ? ['qualifications' => $qualifications->toArray()] : []);

        if ($permissionOption->isEmpty()) {
            // 事業所選択肢一覧の文脈では権限未指定は権限に関係なく取得したいケースを指すため、事業所検索ユースケースではなく OfficeFinder を使用する
            return $this->officeFinder
                ->find($filterParams, ['all' => true, 'sortBy' => 'name'])
                ->list
                ->map(fn (Office $office): OfficeOption => OfficeOption::from($office));
        } else {
            $permission = $permissionOption->head();
            return $this->findOfficeUseCase
                ->handle($context, [$permission], $filterParams, ['all' => true])
                ->list
                ->map(fn (Office $office): OfficeOption => OfficeOption::from($office));
        }
    }
}
