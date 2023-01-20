<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use DateTimeInterface;
use Domain\Enum;
use Domain\ModelCompat;
use JsonSerializable;
use Lib\Arrays;
use Spatie\Snapshots\Drivers\ObjectDriver;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;

final class ZingerModelDriver extends ObjectDriver
{
    /** {@inheritdoc} */
    public function serialize($data): string
    {
        $serializer = $this->makeSerializer();
        return $this->dedent(
            $serializer->serialize($this->toSerializable($data), 'yaml', [
                'yaml_inline' => 12,
                'yaml_indent' => 4,
                'yaml_flags' => Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
            ])
        );
    }

    /** {@inheritdoc} */
    public function extension(): string
    {
        return 'yaml';
    }

    /**
     * シリアライザーを生成する.
     */
    private function makeSerializer(): Serializer
    {
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer(),
        ];
        $encoders = [
            new YamlEncoder(),
        ];
        return new Serializer($normalizers, $encoders);
    }

    /**
     * モデルを再帰的にシリアライズ可能な値に変換する.
     *
     * @param mixed $x
     * @return mixed
     */
    private function toSerializable(mixed $x): mixed
    {
        if ($x instanceof ModelCompat) {
            return $this->toSerializable($x->toAssoc());
        } elseif ($x instanceof Enum) {
            return $x->value();
        } elseif ($x instanceof DateTimeInterface) {
            return $x;
        } elseif (is_iterable($x)) {
            return Arrays::generate(function () use ($x): iterable {
                foreach ($x as $key => $value) {
                    yield $key => $this->toSerializable($value);
                }
            });
        } elseif ($x instanceof JsonSerializable) {
            return $x->jsonSerialize();
        } else {
            return $x;
        }
    }
}
