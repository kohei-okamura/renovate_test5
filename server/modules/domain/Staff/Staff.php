<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Entity;
use Domain\Versionable;

/**
 * スタッフ.
 *
 * @property-read int $organizationId 組織ID
 * @property-read int $bankAccountId 銀行口座ID
 * @property-read int[] $roleIds ロールID
 * @property-read string $employeeNumber 社員番号
 * @property-read \Domain\Common\StructuredName $name 氏名
 * @property-read \Domain\Common\Addr $addr 住所
 * @property-read \Domain\Common\Sex $sex 性別
 * @property-read \Domain\Common\Carbon $birthday 生年月日
 * @property-read \Domain\Common\Location $location 位置情報
 * @property-read string $tel 電話番号
 * @property-read string $fax FAX番号
 * @property-read string $email メールアドレス
 * @property-read \Domain\Common\Password $password パスワード
 * @property-read \Domain\Staff\Certification[] $certifications 資格
 * @property-read int[] $officeIds 事業所ID
 * @property-read int[] $officeGroupIds 事業所グループID
 * @property-read bool $isVerified メールアドレス検証済みフラグ
 * @property-read \Domain\Staff\StaffStatus $status 状態
 * @property-read int $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class Staff extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'employeeNumber',
            'bankAccountId',
            'name',
            'sex',
            'birthday',
            'addr',
            'location',
            'tel',
            'fax',
            'email',
            'password',
            'certifications',
            'roleIds',
            'officeIds',
            'officeGroupIds',
            'isVerified',
            'status',
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
            'employeeNumber' => true,
            'name' => true,
            'sex' => true,
            'birthday' => true,
            'addr' => true,
            'location' => true,
            'tel' => true,
            'fax' => true,
            'email' => true,
            'password' => false,
            'certifications' => true,
            'bankAccountId' => true,
            'roleIds' => true,
            'officeIds' => true,
            'officeGroupIds' => true,
            'isVerified' => true,
            'status' => true,
            'isEnabled' => true,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
