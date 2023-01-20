<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Equatable;
use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use Lib\Exceptions\RuntimeException;

/**
 * パスワード.
 */
final class Password implements Equatable, JsonSerializable
{
    private const ALGORITHM = \PASSWORD_ARGON2ID;
    private string $hashedValue;
    private ?string $rawValue;

    /**
     * Constructor.
     *
     * @param string $hashedValue
     * @param null|string $rawValue
     */
    private function __construct(string $hashedValue, ?string $rawValue = null)
    {
        $this->hashedValue = $hashedValue;
        $this->rawValue = $rawValue;
    }

    /**
     * Create an instance from password string.
     *
     * @param string $password
     * @return static
     */
    public static function fromString(string $password): self
    {
        $hash = password_hash($password, self::ALGORITHM);
        return new self($hash, $password);
    }

    /**
     * Create an instance from hash string.
     *
     * @param string $hashString
     * @return static
     */
    #[Pure]
    public static function fromHashString(string $hashString): self
    {
        return new self($hashString);
    }

    /**
     * Get the value.
     *
     * @return string
     */
    public function value(): string
    {
        if ($this->rawValue === null) {
            throw new RuntimeException('Password has no value');
        }
        return $this->rawValue;
    }

    /**
     * Get the hash string.
     *
     * @return string
     */
    #[Pure]
    public function hashString(): string
    {
        return $this->hashedValue;
    }

    /** {@inheritdoc} */
    public function equals(mixed $that): bool
    {
        return $that instanceof self
            && (
                $this->hashString() === $that->hashString()
                || $this->rawValue === $that->rawValue
                || ($that->rawValue !== null && $this->check($that->rawValue))
                || ($this->rawValue !== null && $that->check($this->rawValue))
            );
    }

    /**
     * Check the hash with given password.
     *
     * @param string $password
     * @return bool
     */
    public function check(string $password): bool
    {
        return password_verify($password, $this->hashString());
    }

    /** {@inheritdoc} */
    public function jsonSerialize(): string
    {
        return '********';
    }
}
