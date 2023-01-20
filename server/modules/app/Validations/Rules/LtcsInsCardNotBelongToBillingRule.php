<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\LtcsBillingStatementFinder;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\LtcsInsCard\LookupLtcsInsCardUseCase;

/**
 * 入力値の介護保険被保険者証が請求に紐付いていないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait LtcsInsCardNotBelongToBillingRule
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
    protected function validateLtcsInsCardNotBelongToBilling(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'ltcs_ins_card_not_belong_to_billing');
        $userId = (int)Arr::get($this->data, $parameters[0]);
        $permission = Permission::from((string)$parameters[1]);

        $useCase = app(LookupLtcsInsCardUseCase::class);
        assert($useCase instanceof LookupLtcsInsCardUseCase);
        return $useCase->handle($this->context, $permission, $userId, (int)$value)
            ->headOption()
            ->map(function (LtcsInsCard $x) use ($userId): bool {
                $finder = app(LtcsBillingStatementFinder::class);
                assert($finder instanceof LtcsBillingStatementFinder);
                return $finder->find(['userId' => $userId], ['all' => true, 'sortBy' => 'id'])
                    ->list
                    ->isEmpty();
            })
            ->getOrElseValue(true);
    }
}
