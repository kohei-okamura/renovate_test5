<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use ScalikePHP\Seq;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書 CSV.
 */
final class LtcsHomeVisitLongTermCareDictionaryCsv
{
    private iterable $csv;

    /**
     * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv} constructor.
     *
     * @param iterable $csv
     */
    private function __construct(iterable $csv)
    {
        $this->csv = $csv;
    }

    /**
     * インスタンスを生成する.
     *
     * @param iterable $csv
     * @return static
     */
    public static function create(iterable $csv): self
    {
        return new self($csv);
    }

    /**
     * CSV の各行を取得する.
     *
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow[]|\ScalikePHP\Seq
     */
    public function rows(): Seq
    {
        $g = call_user_func(function (): iterable {
            foreach ($this->csv as $row) {
                yield LtcsHomeVisitLongTermCareDictionaryCsvRow::create($row);
            }
        });
        return Seq::fromArray($g);
    }
}
