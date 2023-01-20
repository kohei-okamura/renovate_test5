<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Spreadsheet\ShiftWorksheet;
use Domain\Validator\ImportShiftAsyncValidator;
use Illuminate\Support\Str;
use ScalikePHP\Seq;

/**
 * 勤務シフト一括登録非同期処理用バリデータ実装.
 */
class ImportShiftAsyncValidatorImpl extends AsyncValidatorImpl implements ImportShiftAsyncValidator
{
    /** {@inheritdoc} */
    protected function rules(): array
    {
        return [
            'officeId' => ['bail', 'required', 'office_exists:' . Permission::createShifts()],
            'shifts.*.isTraining1' => ['nullable', 'in:＊'],
            'shifts.*.isTraining2' => ['nullable', 'in:＊'],
            'shifts.*.serviceCode' => ['nullable', 'string', 'max:6', 'regex:/[A-Z0-9]/'],
            'shifts.*.date' => [
                'bail',
                'required',
                'excel_timestamp',
                'excel_timestamp_after_or_equal:' . Carbon::today()->toDateString(),
            ],
            'shifts.*.notificationEnabled' => ['nullable', 'in:＊'],
            'shifts.*.oneOff' => ['nullable', 'in:＊'],
            'shifts.*.firstTime' => ['nullable', 'in:＊'],
            'shifts.*.emergency' => ['nullable', 'in:＊'],
            'shifts.*.sucking' => ['nullable', 'in:＊'],
            'shifts.*.welfareSpecialistCooperation' => ['nullable', 'in:＊'],
            'shifts.*.plannedByNovice' => ['nullable', 'in:＊'],
            'shifts.*.providedByBeginner' => ['nullable', 'in:＊'],
            'shifts.*.providedByCareWorkerForPwsd' => ['nullable', 'in:＊'],
            'shifts.*.over20' => ['nullable', 'in:＊'],
            'shifts.*.over50' => ['nullable', 'in:＊'],
            'shifts.*.behavioralDisorderSupportCooperation' => ['nullable', 'in:＊'],
            'shifts.*.hospitalized' => ['nullable', 'in:＊'],
            'shifts.*.longHospitalized' => ['nullable', 'in:＊'],
            'shifts.*.coaching' => ['nullable', 'in:＊'],
            'shifts.*.vitalFunctionsImprovement1' => ['nullable', 'in:＊'],
            'shifts.*.vitalFunctionsImprovement2' => ['nullable', 'in:＊'],
            'shifts.*.note' => ['nullable', 'string'],
            'shifts.*.userId' => [
                'bail',
                'nullable',
                'integer',
                'user_exists:' . Permission::createShifts(),
                'user_belongs_to_office:officeId,shifts.*.task,' . Permission::createShifts(),
            ],
            'shifts.*.assigneeId1' => [
                'bail',
                'required',
                'integer',
                'staff_exists:' . Permission::createShifts(),
            ],
            'shifts.*.assigneeId2' => [
                'bail',
                'nullable',
                'integer',
                'different:shifts.*.assigneeId1',
                'staff_exists:' . Permission::createShifts(),
            ],
            'shifts.*.assignerId' => [
                'bail',
                'required',
                'integer',
                'staff_exists:' . Permission::createShifts(),
            ],
            'shifts.*.task' => ['bail', 'required', 'task'],
            'shifts.*.startMinute' => ['bail', 'required', 'integer'],
            'shifts.*.endMinute' => ['bail', 'required', 'integer'],
            'shifts.*.totalDuration' => ['bail', 'required', 'integer', 'confirmed'],
            'shifts.*.dwsHome' => ['bail', 'required', 'integer'],
            'shifts.*.visitingCare' => ['bail', 'required', 'integer'],
            'shifts.*.outingSupport' => ['bail', 'required', 'integer'],
            'shifts.*.physicalCare' => ['bail', 'required', 'integer'],
            'shifts.*.housework' => ['bail', 'required', 'integer'],
            'shifts.*.comprehensive' => ['bail', 'required', 'integer'],
            'shifts.*.commAccompany' => ['bail', 'required', 'integer'],
            'shifts.*.ownExpense' => ['bail', 'required', 'integer'],
            'shifts.*.other' => ['bail', 'required', 'integer'],
            'shifts.*.resting' => ['bail', 'required', 'integer'],
        ];
    }

    /** {@inheritdoc} */
    protected function errorMessage(CustomValidator $validator): Seq
    {
        $message = $validator->errors()->get('officeId')[0] ?? null;
        $officeErrorMessage = $message === null ? Seq::emptySeq() : Seq::from("「事業所名」は{$message}");
        return Seq::merge(
            $officeErrorMessage,
            Seq::fromArray($validator->errors()->keys())
                ->filter(fn (string $key): bool => $key !== 'officeId')
                ->map(function (string $key) use ($validator): string {
                    $message = $validator->errors()->get($key)[0];
                    $column = $this->targetColumn($key);
                    $row = explode('.', $key)[1] + ShiftWorksheet::SHIFT_START_ROW;
                    return "「{$column}」は{$message}（行番号{$row}）";
                })
        );
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'shifts.*.assigneeId1' => '「担当スタッフ: 1」',
        ];
    }

    /**
     * 勤務シフト雛形の対象列名を取得する.
     *
     * @param string $key
     * @return string
     */
    private function targetColumn(string $key): string
    {
        switch ($key) {
            case Str::contains($key, 'isTraining1'):
                return '研修: 1';
            case Str::contains($key, 'isTraining2'):
                return '研修: 2';
            case Str::contains($key, 'serviceCode'):
                return 'サービスコード';
            case Str::contains($key, 'date'):
                return '日付';
            case Str::contains($key, 'notificationEnabled'):
                return '通知';
            case Str::contains($key, 'oneOff'):
                return '単発';
            case Str::contains($key, 'firstTime'):
                return '初回';
            case Str::contains($key, 'emergency'):
                return '緊急時対応';
            case Str::contains($key, 'sucking'):
                return '喀痰吸引';
            case Str::contains($key, 'welfareSpecialistCooperation'):
                return '福祉専門職員等連携';
            case Str::contains($key, 'plannedByNovice'):
                return '初計';
            case Str::contains($key, 'providedByBeginner'):
                return '基礎研修課程修了者等';
            case Str::contains($key, 'providedByCareWorkerForPwsd'):
                return '重研';
            case Str::contains($key, 'over20'):
                return '同一建物減算';
            case Str::contains($key, 'over50'):
                return '同一建物減算（大規模）';
            case Str::contains($key, 'behavioralDisorderSupportCooperation'):
                return '行動障害支援連携';
            case Str::contains($key, 'hospitalized'):
                return '入院';
            case Str::contains($key, 'longHospitalized'):
                return '入院（長期）';
            case Str::contains($key, 'coaching'):
                return '熟練同行';
            case Str::contains($key, 'vitalFunctionsImprovement1'):
                return '生活機能向上連携Ⅰ';
            case Str::contains($key, 'vitalFunctionsImprovement2'):
                return '生活機能向上連携Ⅱ';
            case Str::contains($key, 'note'):
                return '備考';
            case Str::contains($key, 'userId'):
                return '利用者';
            case Str::contains($key, 'assigneeId1'):
                return '担当スタッフ: 1';
            case Str::contains($key, 'assigneeId2'):
                return '担当スタッフ: 2';
            case Str::contains($key, 'assignerId'):
                return '管理スタッフ';
            case Str::contains($key, 'task'):
                return '予定区分';
            case Str::contains($key, 'startMinute'):
                return '開始';
            case Str::contains($key, 'endMinute'):
                return '終了';
            case Str::contains($key, 'totalDuration'):
                return '合計';
            case Str::contains($key, 'dwsHome'):
                return '居宅';
            case Str::contains($key, 'visitingCare'):
                return '重訪';
            case Str::contains($key, 'outingSupport'):
                return '移動加算';
            case Str::contains($key, 'physicalCare'):
                return '介保身体';
            case Str::contains($key, 'housework'):
                return '介保生活';
            case Str::contains($key, 'comprehensive'):
                return '総合事業';
            case Str::contains($key, 'commAccompany'):
                return '移動支援';
            case Str::contains($key, 'ownExpense'):
                return '自費';
            case Str::contains($key, 'other'):
                return 'その他';
            case Str::contains($key, 'resting'):
                return '休憩';
            default:
                return '';
        }
    }
}
