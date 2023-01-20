<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\DwsCertification\DwsCertificationServiceType;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 支給決定期間が重複する重度訪問介護の支給決定内容がないことを検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoDwsCertificationGrantsDuplicateRule
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
    protected function validateNoDwsCertificationGrantsDuplicate(string $attribute, $value, array $parameters): bool
    {
        // 空の場合はここでは true とする.
        if (empty($value)) {
            return true;
        }
        $f = function (array $x): bool {
            return in_array(
                $x['dwsCertificationServiceType'],
                [
                    DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                    DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                    DwsCertificationServiceType::visitingCareForPwsd3()->value(),
                ],
                true
            );
        };
        // 重訪以外の場合はこのバリデーションでは true とする
        if (!$f($value)) {
            return true;
        }

        // すべての支給決定内容
        $grants = Arr::get($this->data, $parameters[0]);

        $thisStart = Carbon::parse($value['activatedOn']);
        $thisEnd = Carbon::parse($value['deactivatedOn']);

        return Seq::fromArray($grants)
            ->map(fn (array $grant, int $index) => compact('grant', 'index'))
            ->forAll(function (array $x) use ($f, $attribute, $thisStart, $thisEnd): bool {
                /** @var int $index */
                $index = $x['index'];
                /** @var array $grant */
                $grant = $x['grant'];
                if (!$f($grant)) {
                    return true;
                }
                // 開始時刻と終了時刻が日付でない場合、ここではエラーとしない
                if (!$this->validateDate($attribute, $grant['activatedOn']) || !$this->validateDate($attribute, $grant['deactivatedOn'])) {
                    return true;
                }

                // 自分自身は検証しない
                if ($index === (int)$this->getExplicitKeys($attribute)[0]) {
                    return true;
                }

                $thatStart = Carbon::parse($grant['activatedOn']);
                $thatEnd = Carbon::parse($grant['deactivatedOn']);
                $thisRange = CarbonRange::create(['start' => $thisStart, 'end' => $thisEnd]);
                $thatRange = CarbonRange::create(['start' => $thatStart, 'end' => $thatEnd]);
                return !$thisRange->isOverlapping($thatRange);
            });
    }
}
