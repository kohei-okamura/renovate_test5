<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 「週間シフト」ワークシートクラス.
 */
final class ShiftWorksheet
{
    public const SHIFT_START_ROW = 9;
    public const OFFICE_ID_CELL = 'B3';

    private Worksheet $worksheet;

    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * 指定した番号の行を取得する.
     *
     * @param int $rowIndex
     * @return \Domain\Spreadsheet\ShiftWorksheetRow
     */
    public function row(int $rowIndex): ShiftWorksheetRow
    {
        return new ShiftWorksheetRow(new Row($this->worksheet, $rowIndex));
    }

    /**
     * 指定した範囲の行を取得する.
     *
     * @param int $startRow
     * @param int $endRow
     * @return iterable
     */
    public function rows(int $startRow, int $endRow): iterable
    {
        foreach ($this->worksheet->getRowIterator($startRow, $endRow) as $rowIndex => $row) {
            yield $this->row($rowIndex);
        }
    }
}
