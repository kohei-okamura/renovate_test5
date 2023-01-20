<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use ScalikePHP\Seq;

/**
 * 障害:居宅サービスコード辞書 CSV クラス.
 */
final class DwsHomeHelpServiceDictionaryCsv
{
    private iterable $csv;

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv} constructor.
     *
     * @param iterable $csv
     */
    public function __construct(iterable $csv)
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
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsvRow[]|\ScalikePHP\Seq
     */
    public function rows(): Seq
    {
        $g = call_user_func(function (): iterable {
            foreach ($this->csv as $row) {
                yield DwsHomeHelpServiceDictionaryCsvRow::create($row);
            }
        });
        return Seq::fromArray($g);
    }
}
