<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\Staff\Invitation;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 招待作成リクエスト.
 *
 * @property-read array&string[] $emails
 * @property-read array&int[] $officeIds
 * @property-read array&int[] $officeGroupIds
 * @property-read array&int[] $roleIds
 */
class CreateInvitationRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 招待を生成する.
     *
     * @return \Domain\Staff\Invitation[]&\ScalikePHP\Seq
     */
    public function payload(): Seq
    {
        return Seq::fromArray($this->emails)->map(fn (string $x): Invitation => Invitation::create([
            'email' => $x,
            'officeIds' => $this->officeIds,
            'officeGroupIds' => $this->officeGroupIds,
            'roleIds' => $this->roleIds,
        ]))->computed();
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'emails' => ['required', 'array', 'min:1'],
            'emails.*' => [
                'required',
                'email',
                'max:255',
                'distinct',
                'email_address_is_not_used_by_any_staff',
            ],
            'officeIds' => ['required', 'office_exists:' . Permission::createStaffs()],
            'officeGroupIds' => ['office_group_exists'],
            'roleIds' => ['required', 'role_exists'],
        ];
    }
}
