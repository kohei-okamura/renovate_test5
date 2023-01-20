<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書.
 *
 * @property-read int $id 辞書ID
 * @property-read \Domain\Common\Carbon $effectivatedOn 適用開始日
 * @property-read string $name 名前
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsVisitingCareForPwsdDictionary extends ServiceCodeDictionary
{
}
