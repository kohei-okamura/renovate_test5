<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Model;

/**
 * 事業所選択肢.
 *
 * @property-read string $keyword キーワード
 * @property-read string $text 表示用テキスト
 * @property-read int $value 値
 */
final class OfficeOption extends Model
{
    /**
     * 事業所モデルからインスタンスを生成する.
     *
     * @param \Domain\Office\Office $office
     * @return static
     */
    public static function from(Office $office): self
    {
        $keywords = [
            $office->name,
            $office->abbr,
            $office->phoneticName,
            $office->corporationName,
            $office->phoneticCorporationName,
        ];
        return new self([
            'text' => empty($office->abbr) ? $office->name : $office->abbr,
            'value' => $office->id,
            'keyword' => trim(implode(' ', $keywords)),
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'keyword',
            'text',
            'value',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'keyword' => true,
            'text' => true,
            'value' => true,
        ];
    }
}
