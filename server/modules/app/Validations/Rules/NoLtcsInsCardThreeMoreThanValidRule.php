<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 同一月に3つ以上の有効な被保険者証がないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoLtcsInsCardThreeMoreThanValidRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateNoLtcsInsCardThreeMoreThanValid(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'no_ltcs_ins_card_three_more_than_valid');
        $userId = Arr::get($this->data, 'userId');
        // 更新時に自身を除く
        $id = Arr::get($this->data, 'id');

        /** @var IdentifyLtcsInsCardUseCase $identifyInsCardUseCase */
        $identifyInsCardUseCase = app(IdentifyLtcsInsCardUseCase::class);
        /** @var \UseCase\User\LookupUserUseCase $lookupUserUseCase */
        $lookupUserUseCase = app(LookupUserUseCase::class);

        if ($userId === null) {
            return true;
        }
        $users = $lookupUserUseCase->handle($this->context, Permission::from((string)$parameters[0]), (int)$userId);
        // 利用者が見つからない場合はこのバリデーションではtrueとする.
        if ($users->isEmpty()) {
            return true;
        }
        $insCard1 = $identifyInsCardUseCase->handle($this->context, $users->headOption()->get(), Carbon::parse($value)->startOfMonth());
        $insCard2 = $identifyInsCardUseCase->handle($this->context, $users->headOption()->get(), Carbon::parse($value)->endOfMonth());
        // どちらかが存在しない場合は重複しないのでtrue
        // どちらも存在するが同じ被保険者証の場合2つなのでtrue
        // どちらも違う被保険者証が存在するかつidがnullの場合は3つになるためfalse
        if ($insCard1->isEmpty() || $insCard2->isEmpty()) {
            return true;
        } elseif ($id === null) {
            return $insCard1->get()->id === $insCard2->get()->id;
        }
        $count = Seq::from($insCard1->get()->id, $insCard2->get()->id, (int)$id)
            ->distinct()
            ->count();

        return $count < 3;
    }
}
