<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Constraints;

use Domain\Equatable;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * 2つの配列が厳密に一致していることを表す制約.
 */
class ArrayStrictEquals extends Constraint
{
    private array $value;

    /**
     * Constructor.
     *
     * @param array $value
     */
    public function __construct(array $value)
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
            $success = is_array($other) && count($this->value) === count($other);
            if ($success) {
                foreach ($this->value as $index => $value) {
                    if (!(
                        $value instanceof Equatable
                        && isset($other[$index])
                        && $other[$index] instanceof Equatable
                        && ($value === $other[$index] || $value->equals($other[$index]))
                    )) {
                        $success = false;
                        break;
                    }
                }
            }
            if ($returnResult) {
                return $success;
            }
            if (!$success) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    $this->exporter()->export($this->value),
                    $this->exporter()->export($other)
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
        return 'is identical to an array';
    }
}
