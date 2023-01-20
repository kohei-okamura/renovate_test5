<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Constraints;

use Domain\Equatable;
use Domain\ModelCompat;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * 2つのモデルオブジェクトが厳密に一致していることをあらわす制約（同一インスタンスかを問わない）.
 */
class ModelStrictEquals extends Constraint
{
    private ModelCompat $value;

    /**
     * Constructor.
     *
     * @param \Domain\ModelCompat $value
     */
    public function __construct(ModelCompat $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     * @return null|bool
     */
    public function evaluate($other, $description = '', $returnResult = false): ?bool
    {
        if ($this->value === $other) {
            if ($returnResult) {
                return true;
            }
        } else {
            $success = $this->value instanceof Equatable
                && $other instanceof Equatable
                && $this->value->equals($other);
            if ($returnResult) {
                return $success;
            }
            if (!$success) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    self::sanitize($this->exporter()->export($this->value)),
                    self::sanitize($this->exporter()->export($other))
                );
                $this->fail($other, $description, $f);
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return 'is identical to an object of class "' . get_class($this->value) . '"';
    }

    /**
     * 比較用の文字列をクリーンアップする.
     *
     * 異なるインスタンスのハッシュが差分として表示される問題に対応する.
     *
     * - 標準の Exporter が出力するハッシュ
     * - Carbon が内部的に持っているハッシュ
     *
     * @param string $exported
     * @return string
     */
    private static function sanitize(string $exported): string
    {
        $patterns = [
            '/ Object &[0-9a-f]{32}/' => '',
            '/\'constructedObjectId\' => \'[0-9a-f]{32}\'/' => '\'constructedObjectId\' => \'__HASH_SANITIZED__\'',
        ];
        return preg_replace(array_keys($patterns), array_values($patterns), $exported);
    }
}
