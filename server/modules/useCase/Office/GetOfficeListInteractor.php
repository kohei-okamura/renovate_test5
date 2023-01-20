<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Office\OfficeFinder;
use Domain\Office\OfficeRepository;
use ScalikePHP\Seq;

/**
 * 事業所情報取得（権限指定なし）ユースケース実装.
 */
class GetOfficeListInteractor implements GetOfficeListUseCase
{
    private OfficeFinder $officeFinder;
    private OfficeRepository $officeRepository;

    /**
     * constructor.
     *
     * @param \Domain\Office\OfficeFinder $officeFinder
     * @param \Domain\Office\OfficeRepository $officeRepository
     */
    public function __construct(OfficeFinder $officeFinder, OfficeRepository $officeRepository)
    {
        $this->officeFinder = $officeFinder;
        $this->officeRepository = $officeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, int ...$ids): Seq
    {
        if (count($ids) > 0) {
            return $this->officeRepository
                ->lookup(...$ids)
                ->filter(fn (Office $x): bool => $x->organizationId === $context->organization->id);
        } else {
            return $this->officeFinder
                ->find(
                    ['organizationId' => $context->organization->id],
                    ['all' => true, 'sortBy' => 'name']
                )
                ->list;
        }
    }
}
