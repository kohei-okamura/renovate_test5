<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\User;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\BankAccount\BankAccount;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\DwsCertification\DwsCertification;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Project\DwsProject;
use Domain\Project\LtcsProject;
use Domain\Staff\Staff;
use Domain\User\User;
use Domain\User\UserDwsCalcSpec;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserLtcsCalcSpec;
use Domain\User\UserLtcsSubsidy;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * User get のテスト.
 * GET /users/{id}
 */
class GetUserCest extends UserTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];

        $I->sendGET("users/{$user->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseJson($this->domainToArray($this->expected($staff, $user, true)));
        $I->seeLogCount(0);
    }

    /**
     * 取得権限のない値は空で返却するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICallWithEmptyArraysWhenIsNotGivenSpeifiedPermissions(ApiTester $I)
    {
        $I->wantTo('succeed api call with empty arrays when is not given speified permissions');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $expectedContracts = Seq::fromArray($this->examples->contracts)
            ->filter(fn (Contract $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Contract $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->filter(fn (Contract $x): bool => $x->userId === $user->id)
            ->filter(
                fn (Contract $x): bool => !in_array(
                    $x->serviceSegment,
                    [ServiceSegment::disabilitiesWelfare(), ServiceSegment::longTermCare()],
                    true
                )
            )
            ->sortBy(fn (Contract $x): Carbon => $x->createdAt);
        $expected = $this->expected($staff, $user);
        $expected['bankAccount'] = null;
        $expected['contracts'] = $expectedContracts;
        $expected['dwsCertifications'] = [];
        $expected['dwsProjects'] = [];
        $expected['ltcsInsCards'] = [];
        $expected['ltcsProjects'] = [];

        $I->sendGET("users/{$user->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseJson($this->domainToArray($expected));
        $I->seeLogCount(0);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotExist(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not exist.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "user with id {$id} not found");
    }

    /**
     * 事業者が異なる利用者のIDを指定すると404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when User is outside Organization');

        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);
        $id = $this->examples->users[14]->id;

        $I->sendGET("users/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "user with id {$id} not found");
    }

    /**
     * ユーザー情報が含まれているかのテスト.
     *
     * @param ApiTester $I
     */
    public function returnValuesContainsParameter(ApiTester $I)
    {
        $I->wantTo('return values contains Parameter');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $dwsProject = $this->domainToArray($this->examples->dwsProjects[0]);
        $ltcsProject = $this->domainToArray($this->examples->ltcsProjects[0]);

        $I->sendGET("users/{$user->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($dwsProject);
        $I->seeResponseContainsJson($ltcsProject);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能なOfficeでない（Officeと契約がない）利用者を指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is not in accessible Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->users[1]->id;

        $I->sendGET("users/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogMessage(0, LogLevel::WARNING, "user with id {$id} not found");
        $I->seeLogCount(1);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $user = $this->examples->users[0];

        $I->sendGET("users/{$user->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * テストの期待値を返却する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param \Domain\User\User $user
     * @param bool $isAdmin StaffがAdminかどうか
     * @return array
     */
    private function expected(Staff $staff, User $user, bool $isAdmin = false): array
    {
        $bankAccount = Seq::fromArray($this->examples->bankAccounts)
            ->find(fn (BankAccount $x): bool => $x->id === $user->bankAccountId)
            ->head();
        $contracts = Seq::fromArray($this->examples->contracts)
            ->filter(fn (Contract $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Contract $x): bool => $isAdmin ? true : in_array($x->officeId, $staff->officeIds, true))
            ->filter(fn (Contract $x): bool => $x->userId === $user->id)
            ->sortBy(fn (Contract $x): Carbon => $x->createdAt);
        $dwsCalcSpecs = Seq::fromArray($this->examples->userDwsCalcSpecs)
            ->filter(fn (UserDwsCalcSpec $x): bool => $x->userId === $user->id)
            ->sortBy(fn (UserDwsCalcSpec $x): Carbon => $x->effectivatedOn)
            ->reverse();
        $dwsCertifications = Seq::fromArray($this->examples->dwsCertifications)
            ->filter(fn (DwsCertification $x): bool => $x->userId === $user->id)
            ->sortBy(fn (DwsCertification $x): Carbon => $x->createdAt);
        $dwsSubsidies = Seq::fromArray($this->examples->userDwsSubsidies)
            ->filter(fn (UserDwsSubsidy $x): bool => $x->userId === $user->id)
            ->sortBy(fn (UserDwsSubsidy $x): int => $x->id);
        $ltcsInsCards = Seq::fromArray($this->examples->ltcsInsCards)
            ->filter(fn (LtcsInsCard $x): bool => $x->userId === $user->id)
            ->sortBy(fn (LtcsInsCard $x): Carbon => $x->createdAt);
        $dwsProjects = Seq::fromArray($this->examples->dwsProjects)
            ->filter(fn (DwsProject $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (DwsProject $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->filter(fn (DwsProject $x): bool => $x->userId === $user->id)
            ->sortBy(fn (DwsProject $x): Carbon => $x->createdAt);
        $ltcsCalcSpecs = Seq::fromArray($this->examples->userLtcsCalcSpecs)
            ->filter(fn (UserLtcsCalcSpec $x): bool => $x->userId === $user->id)
            ->sortBy(fn (UserLtcsCalcSpec $x): Carbon => $x->effectivatedOn)
            ->reverse();
        $ltcsProjects = Seq::fromArray($this->examples->ltcsProjects)
            ->filter(fn (LtcsProject $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (LtcsProject $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->filter(fn (LtcsProject $x): bool => $x->userId === $user->id)
            ->sortBy(fn (LtcsProject $x): Carbon => $x->createdAt);
        $ltcsSubsidies = Seq::fromArray($this->examples->userLtcsSubsidies)
            ->filter(fn (UserLtcsSubsidy $x): bool => $x->userId === $user->id)
            ->sortBy(fn (UserLtcsSubsidy $x): int => $x->id);

        return compact(
            'bankAccount',
            'contracts',
            'dwsCalcSpecs',
            'dwsCertifications',
            'dwsProjects',
            'dwsSubsidies',
            'ltcsCalcSpecs',
            'ltcsInsCards',
            'ltcsProjects',
            'ltcsSubsidies',
            'user',
        );
    }
}
