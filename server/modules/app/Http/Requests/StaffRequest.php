<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\HttpContext;
use Domain\Context\Context;
use Domain\Staff\Staff;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * スタッフ情報を持つリクエスト.
 */
class StaffRequest extends OrganizationRequest
{
    private ?Staff $staff = null;

    /** @var \Domain\Role\Role[]|\ScalikePHP\Seq */
    private Seq $roles;

    /** @var \Domain\Office\Office[]|\ScalikePHP\Seq 所属しているOffice */
    private Seq $offices;

    /** @var \Domain\Office\Office[]|\ScalikePHP\Seq 所属グループのOffice */
    private Seq $groupOffices;

    /**
     * 事前準備処理.
     *
     * @param \App\Http\Requests\StaffRequest $request
     * @param \Domain\Staff\Staff $staff
     * @param \Domain\Role\Role[]|\ScalikePHP\Seq $roles
     * @param \Domain\Office\Office[]|\ScalikePHP\Seq $offices
     * @param \Domain\Office\Office[]|\ScalikePHP\Seq $groupOffices
     * @return void
     */
    public static function prepareStaffRequest(
        StaffRequest $request,
        Staff $staff,
        Seq $roles,
        Seq $offices,
        Seq $groupOffices
    ): void {
        $request->staff = $staff;
        $request->roles = $roles;
        $request->offices = $offices;
        $request->groupOffices = $groupOffices;
    }

    /** {@inheritdoc} */
    protected function createContext(): Context
    {
        return new HttpContext(
            $this->organization(),
            $this->staff(),
            $this->roles(),
            $this->baseUri(),
            $this->offices,
            $this->groupOffices
        );
    }

    /**
     * ロール情報.
     *
     * @return \Domain\Role\Role|\ScalikePHP\Seq
     */
    protected function roles(): Seq
    {
        return $this->roles;
    }

    /**
     * スタッフ情報.
     *
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    protected function staff(): Option
    {
        return Option::from($this->staff)->orElse(function (): void {
            throw new LogicException('StaffRequest does not have any staff instance.');
        });
    }
}
