<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use InvalidArgumentException;
use JsonSerializable;
use Lib\Arrays;

/**
 * 列挙型抽象基底クラス.
 */
abstract class Enum implements Equatable, JsonSerializable
{
    /** @var int[]|string[] */
    protected static array $values = [];

    /** @var static[][] */
    protected static array $instances = [];

    private string $key;

    /** @var int|string */
    private int|string $value;

    /**
     * Enum constructor.
     *
     * @param string $key
     * @param int|string $value
     */
    protected function __construct(string $key, int|string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Return number of values.
     *
     * @return int
     */
    public static function length(): int
    {
        return count(static::$values);
    }

    /**
     * Validate given value is valid enum value.
     *
     * @param null|int|string $value
     * @return bool
     */
    public static function isValid(int|string|null $value): bool
    {
        return in_array($value, static::$values, true);
    }

    /**
     * Get the enum instance from value.
     *
     * @param int|string $value
     * @return static
     */
    public static function from(int|string $value): self
    {
        $key = array_search($value, static::$values, true);
        if ($key === false) {
            throw new InvalidArgumentException("{$value} is not valid enum value");
        }
        return static::get($key);
    }

    /**
     * Returns all enum instances.
     *
     * @return static[]
     */
    public static function all(): array
    {
        return Arrays::generate(function () {
            foreach (static::$values as $value) {
                yield static::from($value);
            }
        });
    }

    /**
     * Returns the enum value.
     *
     * @return int|string
     */
    public function value(): int|string
    {
        return $this->value;
    }

    /** {@inheritdoc} */
    public function equals(mixed $that): bool
    {
        return $this === $that;
    }

    /**
     * Returns the enum key.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /** {@inheritdoc} */
    public function jsonSerialize(): string|int
    {
        return $this->value;
    }

    /**
     * Get the enum instance from key.
     *
     * @param string $key
     * @return static
     */
    protected static function get(string $key): self
    {
        if (!array_key_exists($key, static::$values)) {
            throw new InvalidArgumentException("{$key} is not valid enum key");
        }
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = [];
        }
        if (!isset(static::$instances[static::class][$key])) {
            static::$instances[static::class][$key] = new static($key, static::$values[$key]);
        }
        return static::$instances[static::class][$key];
    }

    /**
     * Returns a value when called statically like so: `MyEnum::someValue()` given someValue is a key of `static::$value`.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public static function __callStatic(string $name, array $arguments): self
    {
        return static::get($name);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * `clone` されないようにするためにプライベートにする.
     *
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }
}
