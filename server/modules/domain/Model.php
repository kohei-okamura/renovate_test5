<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use DeepCopy\DeepCopy;
use Domain\Common\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use JsonSerializable;
use Lib\Arrays;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\UndefinedPropertyException;
use Lib\Exceptions\UnsupportedOperationException;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Domain Model Base Class.
 */
abstract class Model implements Equatable, Jsonable, JsonSerializable, ModelCompat
{
    use PoliteComparison;

    /**
     * All of the values set on the model instance.
     */
    protected array $values = [];

    private static ?DeepCopy $deepCopy = null;

    /**
     * Create a new model instance.
     *
     * @param array $values
     * @return void
     */
    protected function __construct(array $values = [])
    {
        $defaults = Option::from($this->defaults());
        $xs = Arrays::generate(function () use ($values, $defaults) {
            foreach ($this->attrs() as $attr) {
                $default = $defaults->pick($attr)->orNull();
                yield $attr => Arr::get($values, $attr, $default);
            }
        });
        $this->values = $this->computedAttrs($xs) + $xs + $this->defaults();
    }

    /**
     * Create a new model instance.
     *
     * @param array $values
     * @param bool $strict
     * @return static
     */
    public static function create(array $values = [], bool $strict = false): self
    {
        $instance = new static($values);
        if ($strict) {
            foreach ($values as $key => $v) {
                $instance->get($key);
            }
        }
        return $instance;
    }

    /**
     * Create a copy of instance.
     *
     * @param array $values
     * @return static
     */
    public function copy(array $values = []): self
    {
        return new static($values + $this->values);
    }

    /**
     * Get an attr from the model instance.
     *
     * @param string $key
     * @return mixed
     */
    final public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        } else {
            throw new UndefinedPropertyException("Undefined property: {$key}");
        }
    }

    /** {@inheritdoc} */
    public function toAssoc(): array
    {
        return $this->values;
    }

    /** {@inheritdoc} */
    final public function toJson($options = 0): string
    {
        return Json::encode($this, $options | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
    }

    /** {@inheritdoc} */
    public function jsonSerialize(): mixed
    {
        return Arrays::generate(function () {
            $jsonables = $this->jsonables();
            foreach ($this->values as $key => $value) {
                assert(isset($jsonables[$key]), "Jsonables is not defined sufficiently: undefined index '{$key}'");
                if ($jsonables[$key] === 'date') {
                    if ($value === null) {
                        yield $key => null;
                    } else {
                        if (is_array($value)) {
                            $values = Seq::fromArray($value);
                            assert(
                                $values->forAll(fn ($x): bool => $x instanceof Carbon),
                                '`date` property should be Carbon.'
                            );
                            yield $key => $values->map(fn (Carbon $x): string => $x->toDateString());
                        } else {
                            assert($value instanceof Carbon, '`date` property should be Carbon.');
                            yield $key => $value->toDateString();
                        }
                    }
                } elseif ($jsonables[$key]) {
                    yield $key => $value;
                }
            }
        });
    }

    /** {@inheritdoc} */
    public function equals(mixed $that): bool
    {
        return $that instanceof static
            && Seq::fromArray($this->attrs())->forAll(function (string $attr) use ($that): bool {
                return self::compare($this->{$attr}, $that->{$attr});
            });
    }

    /**
     * Return name of attrs.
     *
     * @return string[]
     */
    abstract protected function attrs(): array;

    /**
     * Return jsonable attr names.
     * NOTE: All sub classes should overridden this method.
     *
     * @codeCoverageIgnore
     *
     * @return array
     */
    protected function jsonables(): array
    {
        throw new LogicException(static::class . '::jsonables() should overridden');
    }

    /**
     * Return computed values.
     *
     * @param array $values
     * @return array
     */
    protected function computedAttrs(array $values): array
    {
        return [];
    }

    /**
     * Return default vales.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Get an attr from the model instance.
     *
     * @param string $key
     * @return mixed
     */
    final public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Set an attr from the model instance.
     *
     * @codeCoverageIgnore
     *
     * @param string $key
     * @param mixed $value
     * @return never
     */
    final public function __set(string $key, mixed $value): never
    {
        $class = get_class($this);
        throw new UnsupportedOperationException("Unsupported operation: {$class} is immutable");
    }

    /**
     * Dynamically check if an attr is set.
     *
     * @param string $key
     * @return bool
     */
    final public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Returns the long description as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson(\JSON_PRETTY_PRINT);
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
