<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCode;

use BadMethodCallException;
use Domain\Model;
use Lib\Exceptions\RuntimeException;

/**
 * サービスコード.
 *
 * @property-read string $serviceDivisionCode サービス種類コード
 * @property-read string $serviceCategoryCode サービス項目コード
 */
final class ServiceCode extends Model
{
    /**
     * サービスコード文字列からインスタンスを生成する.
     *
     * @param string $serviceCodeString サービスコード文字列
     * @return static
     */
    public static function fromString(string $serviceCodeString): self
    {
        if (preg_match('/\A([A-Z0-9]{2})([A-Z0-9]{4})\z/', $serviceCodeString, $m) !== 1) {
            throw new RuntimeException("Invalid service code string: {$serviceCodeString}");
        }
        return self::create([
            'serviceDivisionCode' => $m[1],
            'serviceCategoryCode' => $m[2],
        ]);
    }

    /**
     * インスタンスからサービスコード文字列を生成する.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->serviceDivisionCode . $this->serviceCategoryCode;
    }

    /** {@inheritdoc} */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'serviceDivisionCode',
            'serviceCategoryCode',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore 使用されないようにするため
     */
    protected function jsonables(): array
    {
        throw new BadMethodCallException('DO NOT CALL ServiceCode::jsonables()');
    }
}
