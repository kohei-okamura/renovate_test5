<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 「週間シフト」ワークシート行クラス.
 */
final class ShiftWorksheetRow
{
    public const COLUMN_IS_TRAINING1 = 'C';
    public const COLUMN_IS_TRAINING2 = 'E';
    public const COLUMN_SERVICE_CODE = 'G';
    public const COLUMN_DATE = 'H';
    public const COLUMN_NOTIFICATION_ENABLED = 'X';
    public const COLUMN_ONE_OFF = 'Y';
    public const COLUMN_FIRST_TIME = 'Z';
    public const COLUMN_EMERGENCY = 'AA';
    public const COLUMN_SUCKING = 'AB';
    public const COLUMN_WELFARE_SPECIALIST_COOPERATION = 'AC';
    public const COLUMN_PLANNED_BY_NOVICE = 'AD';
    public const COLUMN_PROVIDED_BY_BEGINNER = 'AE';
    public const COLUMN_PROVIDED_BY_CARE_WORKER_FOR_PWSD = 'AF';
    public const COLUMN_OVER20 = 'AG';
    public const COLUMN_OVER50 = 'AH';
    public const COLUMN_BEHAVIORAL_DISORDER_SUPPORT_COOPERATION = 'AI';
    public const COLUMN_HOSPITALIZED = 'AJ';
    public const COLUMN_LONG_HOSPITALIZED = 'AK';
    public const COLUMN_COACHING = 'AL';
    public const COLUMN_VITAL_FUNCTIONS_IMPROVEMENT1 = 'AM';
    public const COLUMN_VITAL_FUNCTIONS_IMPROVEMENT2 = 'AN';
    public const COLUMN_NOTE = 'AP';
    public const COLUMN_USER_ID = 'AQ';
    public const COLUMN_ASSIGNEE_ID1 = 'AR';
    public const COLUMN_ASSIGNEE_ID2 = 'AS';
    public const COLUMN_ASSIGNER_ID = 'AT';
    public const COLUMN_TASK = 'AU';
    public const COLUMN_START_MINUTE = 'AV';
    public const COLUMN_END_MINUTE = 'AW';
    public const COLUMN_TOTAL_DURATION = 'AX';
    public const COLUMN_DWS_HOME = 'AY';
    public const COLUMN_VISITING_CARE = 'AZ';
    public const COLUMN_OUTING_SUPPORT = 'BA';
    public const COLUMN_PHYSICAL_CARE = 'BB';
    public const COLUMN_HOUSEWORK = 'BC';
    public const COLUMN_COMPREHENSIVE = 'BD';
    public const COLUMN_COMM_ACCOMPANY = 'BE';
    public const COLUMN_OWN_EXPENSE = 'BF';
    public const COLUMN_OTHER = 'BG';
    public const COLUMN_RESTING = 'BH';

    private Row $row;
    private ?Worksheet $worksheet;

    public function __construct(Row $row)
    {
        $this->row = $row;
        $this->worksheet = $row->getWorksheet();
    }

    /**
     * 「研修: 1」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function isTraining1(): ?Cell
    {
        return $this->getCell(self::COLUMN_IS_TRAINING1);
    }

    /**
     * 「研修: 2」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function isTraining2(): ?Cell
    {
        return $this->getCell(self::COLUMN_IS_TRAINING2);
    }

    /**
     * 「サービスコード」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function serviceCode(): ?Cell
    {
        return $this->getCell(self::COLUMN_SERVICE_CODE);
    }

    /**
     * 「勤務日」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function date(): ?Cell
    {
        return $this->getCell(self::COLUMN_DATE);
    }

    /**
     * 「通知」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function notificationEnabled(): ?Cell
    {
        return $this->getCell(self::COLUMN_NOTIFICATION_ENABLED);
    }

    /**
     * 「単発」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function oneOff(): ?Cell
    {
        return $this->getCell(self::COLUMN_ONE_OFF);
    }

    /**
     * 「初回」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function firstTime(): ?Cell
    {
        return $this->getCell(self::COLUMN_FIRST_TIME);
    }

    /**
     * 「緊急時対応」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function emergency(): ?Cell
    {
        return $this->getCell(self::COLUMN_EMERGENCY);
    }

    /**
     * 「喀痰吸引」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function sucking(): ?Cell
    {
        return $this->getCell(self::COLUMN_SUCKING);
    }

    /**
     * 「福祉専門職員等連携」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function welfareSpecialistCooperation(): ?Cell
    {
        return $this->getCell(self::COLUMN_WELFARE_SPECIALIST_COOPERATION);
    }

    /**
     * 「初計」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function plannedByNovice(): ?Cell
    {
        return $this->getCell(self::COLUMN_PLANNED_BY_NOVICE);
    }

    /**
     * 「基礎研修課程修了者等」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function providedByBeginner(): ?Cell
    {
        return $this->getCell(self::COLUMN_PROVIDED_BY_BEGINNER);
    }

    /**
     * 「重研」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function providedByCareWorkerForPwsd(): ?Cell
    {
        return $this->getCell(self::COLUMN_PROVIDED_BY_CARE_WORKER_FOR_PWSD);
    }

    /**
     * 「同一建物減算」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function over20(): ?Cell
    {
        return $this->getCell(self::COLUMN_OVER20);
    }

    /**
     * 「同一建物減算（大規模）」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function over50(): ?Cell
    {
        return $this->getCell(self::COLUMN_OVER50);
    }

    /**
     * 「行動障害支援連携」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function behavioralDisorderSupportCooperation(): ?Cell
    {
        return $this->getCell(self::COLUMN_BEHAVIORAL_DISORDER_SUPPORT_COOPERATION);
    }

    /**
     * 「入院」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function hospitalized(): ?Cell
    {
        return $this->getCell(self::COLUMN_HOSPITALIZED);
    }

    /**
     * 「入院（長期）」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function longHospitalized(): ?Cell
    {
        return $this->getCell(self::COLUMN_LONG_HOSPITALIZED);
    }

    /**
     * 「熟練同行」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function coaching(): ?Cell
    {
        return $this->getCell(self::COLUMN_COACHING);
    }

    /**
     * 「生活機能向上連携Ⅰ」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function vitalFunctionsImprovement1(): ?Cell
    {
        return $this->getCell(self::COLUMN_VITAL_FUNCTIONS_IMPROVEMENT1);
    }

    /**
     * 「生活機能向上連携Ⅱ」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function vitalFunctionsImprovement2(): ?Cell
    {
        return $this->getCell(self::COLUMN_VITAL_FUNCTIONS_IMPROVEMENT2);
    }

    /**
     * 「備考」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function note(): ?Cell
    {
        return $this->getCell(self::COLUMN_NOTE);
    }

    /**
     * 「利用者ID」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function userId(): ?Cell
    {
        return $this->getCell(self::COLUMN_USER_ID);
    }

    /**
     * 「スタッフID1」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function assigneeId1(): ?Cell
    {
        return $this->getCell(self::COLUMN_ASSIGNEE_ID1);
    }

    /**
     * 「スタッフID2」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function assigneeId2(): ?Cell
    {
        return $this->getCell(self::COLUMN_ASSIGNEE_ID2);
    }

    /**
     * 「入力担当ID」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function assignerId(): ?Cell
    {
        return $this->getCell(self::COLUMN_ASSIGNER_ID);
    }

    /**
     * 「勤務区分」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function task(): ?Cell
    {
        return $this->getCell(self::COLUMN_TASK);
    }

    /**
     * 「開始[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function startMinute(): ?Cell
    {
        return $this->getCell(self::COLUMN_START_MINUTE);
    }

    /**
     * 「終了[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function endMinute(): ?Cell
    {
        return $this->getCell(self::COLUMN_END_MINUTE);
    }

    /**
     * 「時間[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function totalDuration(): ?Cell
    {
        return $this->getCell(self::COLUMN_TOTAL_DURATION);
    }

    /**
     * 「居宅[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function dwsHome(): ?Cell
    {
        return $this->getCell(self::COLUMN_DWS_HOME);
    }

    /**
     * 「重訪[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function visitingCare(): ?Cell
    {
        return $this->getCell(self::COLUMN_VISITING_CARE);
    }

    /**
     * 「移動加算[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function outingSupport(): ?Cell
    {
        return $this->getCell(self::COLUMN_OUTING_SUPPORT);
    }

    /**
     * 「介保身体[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function physicalCare(): ?Cell
    {
        return $this->getCell(self::COLUMN_PHYSICAL_CARE);
    }

    /**
     * 「介保生活[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function housework(): ?Cell
    {
        return $this->getCell(self::COLUMN_HOUSEWORK);
    }

    /**
     * 「総合事業[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function comprehensive(): ?Cell
    {
        return $this->getCell(self::COLUMN_COMPREHENSIVE);
    }

    /**
     * 「移動支援[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function commAccompany(): ?Cell
    {
        return $this->getCell(self::COLUMN_COMM_ACCOMPANY);
    }

    /**
     * 「自費[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function ownExpense(): ?Cell
    {
        return $this->getCell(self::COLUMN_OWN_EXPENSE);
    }

    /**
     * 「その他[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function other(): ?Cell
    {
        return $this->getCell(self::COLUMN_OTHER);
    }

    /**
     * 「休憩[分]」のセルを取得する.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|\PhpOffice\PhpSpreadsheet\Cell\Cell
     */
    public function resting(): ?Cell
    {
        return $this->getCell(self::COLUMN_RESTING);
    }

    /**
     * 指定した列番号のセルを取得する.
     *
     * @param string $column
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return null|Cell
     */
    private function getCell(string $column): ?Cell
    {
        return $this->worksheet->getCell(
            Coordinate::cell($column, $this->row->getRowIndex())
        );
    }
}
