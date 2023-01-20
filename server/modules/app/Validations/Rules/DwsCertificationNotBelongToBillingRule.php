<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsBillingStatementFinder;
use Domain\DwsCertification\DwsCertification;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\DwsCertification\LookupDwsCertificationUseCase;

/**
 * 入力値の受給者証が請求に紐付いていないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsCertificationNotBelongToBillingRule
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
    protected function validateDwsCertificationNotBelongToBilling(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'dws_certification_not_belong_to_billing');
        $userId = (int)Arr::get($this->data, $parameters[0]);
        $permission = Permission::from((string)$parameters[1]);

        $useCase = app(LookupDwsCertificationUseCase::class);
        assert($useCase instanceof LookupDwsCertificationUseCase);
        return $useCase->handle($this->context, $permission, $userId, (int)$value)
            ->headOption()
            ->map(function (DwsCertification $x): bool {
                $finder = app(DwsBillingStatementFinder::class);
                assert($finder instanceof DwsBillingStatementFinder);
                return $finder->find(['dwsCertificationId' => $x->id], ['all' => true, 'sortBy' => 'id'])
                    ->list
                    ->isEmpty();
            })
            ->getOrElseValue(true);
    }
}
