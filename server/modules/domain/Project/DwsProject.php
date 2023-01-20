<?php
/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Entity;
use Domain\Versionable;

/**
 * 障害福祉サービス：計画.
 *
 * @property-read int $organizationId 事業者 ID
 * @property-read int $contractId 契約 ID
 * @property-read int $officeId 事業所 ID
 * @property-read int $userId 利用者 ID
 * @property-read int $staffId 作成者 ID
 * @property-read \Domain\Common\Carbon $writtenOn 作成日
 * @property-read \Domain\Common\Carbon $effectivatedOn 適用日
 * @property-read string $requestFromUser ご本人の希望
 * @property-read string $requestFromFamily ご家族の希望
 * @property-read string $objective 援助目標
 * @property-read \Domain\Project\DwsProjectProgram[] $programs 週間サービス計画
 * @property-read bool $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsProject extends Entity
{
    use Versionable;

    public const A4_HEIGHT = 292; // .sheet に定義した height
    public const PADDING = 10;
    public const CELL_HEIGHT = 5;
    public const MARGIN_BOTTOM = 5;
    public const BORDER_HEIGHT = 0.26458333333333334; // dpi: 96(laravel dom の デフォルト) 時の 1px の高さ(mm)
    public const FIRST_PAGE = 1;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'contractId',
            'officeId',
            'userId',
            'staffId',
            'writtenOn',
            'effectivatedOn',
            'requestFromUser',
            'requestFromFamily',
            'objective',
            'programs',
            'isEnabled',
            'version',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'organizationId' => false,
            'contractId' => true,
            'officeId' => true,
            'userId' => true,
            'staffId' => true,
            'writtenOn' => true,
            'effectivatedOn' => true,
            'requestFromUser' => true,
            'requestFromFamily' => true,
            'objective' => true,
            'programs' => true,
            'isEnabled' => true,
            'version' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
