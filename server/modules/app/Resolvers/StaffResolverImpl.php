<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Resolvers;

use App\Http\Requests\StaffRequest;
use Domain\Config\Config;
use Domain\Office\OfficeFinder;
use Domain\Office\OfficeGroup;
use Domain\Office\OfficeGroupFinder;
use Domain\Office\OfficeGroupRepository;
use Domain\Office\OfficeRepository;
use Domain\Role\RoleRepository;
use Domain\Staff\Staff;
use Domain\Staff\StaffRememberToken;
use Domain\Staff\StaffRememberTokenRepository;
use Domain\Staff\StaffRepository;
use JsonException;
use Laravel\Lumen\Http\Request as LumenRequest;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * StaffResolver Implementation.
 */
final class StaffResolverImpl implements StaffResolver
{
    private Config $config;
    private OfficeFinder $officeFinder;
    private OfficeGroupFinder $officeGroupFinder;
    private OfficeGroupRepository $officeGroupRepository;
    private OfficeRepository $officeRepository;
    private RoleRepository $roleRepository;
    private StaffRememberTokenRepository $rememberTokenRepository;
    private StaffRepository $staffRepository;

    /**
     * StaffResolverImpl constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \Domain\Office\OfficeFinder $officeFinder
     * @param \Domain\Office\OfficeGroupFinder $officeGroupFinder
     * @param \Domain\Office\OfficeGroupRepository $officeGroupRepository
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\Role\RoleRepository $roleRepository
     * @param \Domain\Staff\StaffRememberTokenRepository $rememberTokenRepository
     * @param \Domain\Staff\StaffRepository $staffRepository
     */
    public function __construct(
        Config $config,
        OfficeFinder $officeFinder,
        OfficeGroupFinder $officeGroupFinder,
        OfficeGroupRepository $officeGroupRepository,
        OfficeRepository $officeRepository,
        RoleRepository $roleRepository,
        StaffRememberTokenRepository $rememberTokenRepository,
        StaffRepository $staffRepository
    ) {
        $this->config = $config;
        $this->officeFinder = $officeFinder;
        $this->officeGroupFinder = $officeGroupFinder;
        $this->officeGroupRepository = $officeGroupRepository;
        $this->officeRepository = $officeRepository;
        $this->roleRepository = $roleRepository;
        $this->rememberTokenRepository = $rememberTokenRepository;
        $this->staffRepository = $staffRepository;
    }

    /** {@inheritdoc} */
    public function resolve(StaffRequest $request): Option
    {
        return $this->lookupStaffFromSession($request)
            ->orElse(fn (): Option => $this->lookupByRememberToken($request))
            ->map(fn (Staff $staff): Staff => $this->prepareStaffRequest($request, $staff));
    }

    /**
     * セッションからスタッフ情報を取得する.
     *
     * @param \Laravel\Lumen\Http\Request $request
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    private function lookupStaffFromSession(LumenRequest $request): Option
    {
        return Option::from($request->session()->get('staffId'))
            ->flatMap(fn (int $id): Option => $this->lookup($id));
    }

    /**
     * スタッフ情報を取得する.
     *
     * @param int $id
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    private function lookup(int $id): Option
    {
        return $this->staffRepository->lookup($id)
            ->filter(fn (Staff $staff) => $staff->isEnabled && $staff->isVerified)
            ->headOption();
    }

    /**
     * リメンバートークンが格納された Cookie からスタッフ情報を取得する.
     *
     * @param \Laravel\Lumen\Http\Request $request
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    private function lookupByRememberToken(LumenRequest $request): Option
    {
        return $this->parseRememberCookie($request)
            ->flatMap(function (array $values) {
                ['id' => $id, 'staffId' => $staffId, 'token' => $token] = $values;
                return $this->rememberTokenRepository
                    ->lookup($id)
                    ->filter(function (StaffRememberToken $x) use ($staffId, $token) {
                        return $x->staffId === $staffId && $x->token === $token && $x->isNotExpired();
                    })
                    ->headOption();
            })
            ->flatMap(fn (StaffRememberToken $token) => $this->lookup($token->staffId));
    }

    /**
     * Parse remember cookie.
     *
     * @param \Laravel\Lumen\Http\Request $request
     * @return mixed[]|\ScalikePHP\Option
     */
    private function parseRememberCookie(LumenRequest $request): Option
    {
        $cookieName = $this->config->get('zinger.remember_token.cookie_name');
        if ($request->hasCookie($cookieName)) {
            try {
                $x = Json::decode($request->cookie($cookieName), true);
                return Option::from($x)->filter(function ($values) {
                    return is_array($values)
                        && array_key_exists('id', $values)
                        && array_key_exists('staffId', $values)
                        && array_key_exists('token', $values);
                });
            } catch (JsonException $exception) {
                // Nothing to do
            }
        }
        return Option::none();
    }

    /**
     * StaffRequestに必要な情報をセットする.
     *
     * @param \App\Http\Requests\StaffRequest $request
     * @param \Domain\Staff\Staff $staff
     * @return \Domain\Staff\Staff $staffを返す
     */
    private function prepareStaffRequest(StaffRequest $request, Staff $staff): Staff
    {
        $roles = $this->roleRepository->lookup(...$staff->roleIds);
        $offices = $this->officeRepository->lookup(...$staff->officeIds);
        $groupOffices = count($staff->officeGroupIds) > 0 ? $this->lookupGroupOffices(...$staff->officeGroupIds) : Seq::emptySeq();
        StaffRequest::prepareStaffRequest($request, $staff, $roles, $offices, $groupOffices);
        return $staff;
    }

    /**
     * グループに含まれる事業所sを取得する.
     *
     * @param int ...$officeGroupIds 事業所グループのIDs
     * @return \Domain\Office\Office[]|\ScalikePHP\Seq
     */
    private function lookupGroupOffices(int ...$officeGroupIds): Seq
    {
        // Staffが保持している、”所属する”事業所グループs
        $primaryOfficeGroups = $this->officeGroupRepository->lookup(...$officeGroupIds);

        // 子を含めた全ての事業所グループs
        $officeGroups = $this->findAllOfficeGroups($primaryOfficeGroups);

        return $this->officeFinder
            ->find(
                ['officeGroupIds' => $officeGroups->map(fn (OfficeGroup $x): int => $x->id)->toArray()],
                ['all' => true, 'sortBy' => 'id'],
            )
            ->list;
    }

    /**
     * 全ての事業所グループを検索する.
     *
     * @param \Domain\Office\OfficeGroup[]|\ScalikePHP\Seq $officeGroups
     * @return \Domain\Office\OfficeGroup[]|\ScalikePHP\Seq
     */
    private function findAllOfficeGroups(Seq $officeGroups): Seq
    {
        $children = $this->officeGroupFinder
            ->find([
                'parentOfficeGroupIds' => $officeGroups->map(fn (OfficeGroup $x): int => $x->id)->toArray(),
            ], [
                'all' => true,
                'sortBy' => 'id',
            ])->list;
        if ($children->nonEmpty()) {
            return $children->append($officeGroups)
                ->append($this->findAllOfficeGroups($children))
                ->distinctBy(fn (OfficeGroup $x): int => $x->id);
        }
        return $children->append($officeGroups)->distinctBy(fn (OfficeGroup $x): int => $x->id);
    }
}
