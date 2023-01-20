<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Attributes;

use Attribute;

/**
 * Polite で JSON 出力時に出力しないプロパティに付与するためのアトリビュート.
 */
#[Attribute]
final class JsonIgnore
{
}
