<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Closure;
use Domain\Attributes\JsonIgnore;
use Illuminate\Contracts\Support\Jsonable;
use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use Lib\Json;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use ScalikePHP\Seq;

/**
 * PHP 8.1 版ドメインモデル基底クラス.
 */
abstract class Polite implements Equatable, Jsonable, JsonSerializable, ModelCompat
{
    use PoliteComparison;

    /** 属性名の一覧のキャッシュ（メモ） */
    private static array $attrs = [];

    /** JSON に出力しない属性一覧のキャッシュ（メモ） */
    private static array $jsonIgnoredAttrs = [];

    /** リフレクションクラスのキャッシュ（メモ） */
    private static array $reflectionClass = [];

    /**
     * 属性値を変更したコピーを得る.
     *
     * @param array $attributes
     * @return static
     */
    final public function copy(array $attributes): static
    {
        return static::fromAssoc($attributes + $this->toAssocForCopy());
    }

    /**
     * 属性値の連想配列からインスタンスを生成する.
     *
     * **非推奨**
     * `new` によるインスタンス生成を用いるか、どうしても連想配列である必要がある場合のみ `fromAssoc` を用いること.
     *
     * @param array $attributes
     * @return static
     * @deprecated
     */
    public static function create(array $attributes): static
    {
        return static::fromAssoc($attributes);
    }

    /** {@inheritdoc} */
    public function equals(mixed $that): bool
    {
        return $that instanceof static
            && Seq::fromArray($this->attrs())->forAll(
                fn (string $attr): bool => self::compare($this->{$attr}, $that->{$attr})
            );
    }

    /**
     * 属性値の連想配列からインスタンスを生成する.
     *
     * @param array $attributes
     * @return static
     */
    final public static function fromAssoc(array $attributes): static
    {
        return call_user_func_array([static::getReflectionClass(), 'newInstance'], $attributes);
    }

    /** {@inheritdoc} */
    final public function get(string $key): mixed
    {
        // ここで `property_exists` すると private/protected なフィールドが含まれてしまうため
        // `PoliteCompanion` 経由で呼び出す
        return PoliteCompanion::get($this, $key);
    }

    /** {@inheritdoc} */
    public function jsonSerialize(): mixed
    {
        $ignoredAttrs = static::jsonIgnoredAttrs();
        return empty($ignoredAttrs)
            ? $this
            : array_filter(
                $this->toAssoc(),
                fn (string $key): bool => !in_array($key, $ignoredAttrs, true),
                \ARRAY_FILTER_USE_KEY
            );
    }

    /** {@inheritdoc} */
    #[Pure]
    final public function toAssoc(): array
    {
        // ここで `get_object_vars` すると private/protected なフィールドが含まれてしまうため
        // `PoliteCompanion` 経由で呼び出す
        return PoliteCompanion::toAssoc($this);
    }

    /** {@inheritdoc} */
    final public function toJson($options = 0): string
    {
        return Json::encode($this, $options | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
    }

    /**
     * プロパティ名の一覧を取得する.
     *
     * @return string[]
     */
    private function attrs(): array
    {
        return self::memo(
            self::$attrs,
            function (): array {
                return self::$attrs[static::class] = array_map(
                    fn (ReflectionProperty $x): string => $x->getName(),
                    static::getReflectionClass()->getProperties(ReflectionProperty::IS_PUBLIC)
                );
            }
        );
    }

    /**
     * リフレクションクラスを取得する.
     *
     * @return \ReflectionClass
     */
    private static function getReflectionClass(): ReflectionClass
    {
        return self::memo(
            self::$reflectionClass,
            fn (): ReflectionClass => new ReflectionClass(static::class)
        );
    }

    /**
     * JSON エンコード時に対象外とするプロパティ名の一覧を取得する.
     *
     * @return string[]
     */
    private function jsonIgnoredAttrs(): array
    {
        return self::memo(
            self::$jsonIgnoredAttrs,
            function (): array {
                $props = static::getReflectionClass()->getProperties(ReflectionProperty::IS_PUBLIC);
                return Seq::from(...$props)
                    ->filterNot(fn (ReflectionProperty $x): bool => empty($x->getAttributes(JsonIgnore::class)))
                    ->map(fn (ReflectionProperty $x): string => $x->name)
                    ->toArray();
            }
        );
    }

    /**
     * 関数の処理結果をクラスごとにキャッシュ（メモ）する.
     *
     * @param array $memo
     * @param \Closure $f
     * @return mixed
     */
    private static function memo(array &$memo, Closure $f): mixed
    {
        if (empty($memo[static::class])) {
            $memo[static::class] = $f();
        }
        return $memo[static::class];
    }

    /**
     * `copy` 用の連想配列を生成する.
     *
     * `copy` ではコンストラクタで定義されている属性のみが必要とされそれ以外が含まれるとエラーになってしまう.
     * それを防止するためコンストラクタの引数一覧を取得しそこに含まれる属性のみを連想配列形式で返す.
     *
     * @return array
     */
    private function toAssocForCopy(): array
    {
        $constructorKeys = array_map(
            fn (ReflectionParameter $x): string => $x->name,
            static::getReflectionClass()->getConstructor()->getParameters()
        );
        return array_intersect_key($this->toAssoc(), array_flip($constructorKeys));
    }
}
