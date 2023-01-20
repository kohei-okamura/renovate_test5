<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Polite;

/**
 * 氏名.
 */
final class StructuredName extends Polite
{
    /** @var string 表示用氏名 */
    public readonly string $displayName;

    /** @var string フリガナ：表示用氏名 */
    public readonly string $phoneticDisplayName;

    /**
     * {@link \Domain\Common\StructuredName} constructor.
     *
     * @param string $familyName 姓
     * @param string $givenName 名
     * @param string $phoneticFamilyName フリガナ：姓
     * @param string $phoneticGivenName フリガナ：名
     */
    public function __construct(
        public readonly string $familyName,
        public readonly string $givenName,
        public readonly string $phoneticFamilyName,
        public readonly string $phoneticGivenName
    ) {
        $this->displayName = "{$familyName} {$givenName}";
        $this->phoneticDisplayName = "{$phoneticFamilyName} {$phoneticGivenName}";
    }

    /**
     * 空の氏名を生成する.
     */
    public static function empty(): self
    {
        return new self(
            familyName: '',
            givenName: '',
            phoneticFamilyName: '',
            phoneticGivenName: ''
        );
    }
}
