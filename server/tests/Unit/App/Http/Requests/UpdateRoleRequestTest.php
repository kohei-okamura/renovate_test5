<?php

/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateRoleRequest;
use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use Lib\Json;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Test;

/**
 * UpdateRoleRequest のテスト.
 */
class UpdateRoleRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use UnitSupport;

    protected UpdateRoleRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateRoleRequestTest $self): void {
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]))
                ->byDefault();
            $self->request = new UpdateRoleRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $roles = $self->examples->roles[0]->copy([
                'isSystemAdmin' => false,
                'permissions' => [Permission::viewStaffs(), Permission::createStaffs()],
            ]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::from($roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($self->examples->roles[0]->id);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $examples = [
            'when isSystemAdmin is true and permissions is empty' => [
                ['isSystemAdmin' => true, 'permissions' => []],
            ],
            'when isSystemAdmin is false and permissions is not empty' => [
                ['isSystemAdmin' => false, 'permissions' => [Permission::viewStaffs()->value() => true]],
            ],
        ];
        $this->should(
            'return Role',
            function ($data): void {
                $input = $data + $this->defaultInput();
                // リクエスト内容を反映させるために initialize() を利用する
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input)
                );
                $this->assertEquals(
                    $this->expectedPayload($input),
                    $this->request->payload()
                );
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $examples = [
            'when isSystemAdmin is true and permissions is empty' => [
                ['isSystemAdmin' => true, 'permissions' => []],
            ],
            'when isSystemAdmin is false and permissions is not empty' => [
                ['isSystemAdmin' => false, 'permissions' => [Permission::viewStaffs()->value() => true]],
            ],
        ];
        $this->should(
            'succeed when the data passes the validation rules',
            function ($data): void {
                $input = $data + $this->defaultInput();
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->passes());
            },
            compact('examples')
        );
        $examples = [
            'when name is empty' => [
                ['name' => ['入力してください。']],
                ['name' => ''],
                ['name' => 'スタッフ参照ロール'],
            ],
            'when name is longer than 100' => [
                ['name' => ['100文字以内で入力してください。']],
                ['name' => str_repeat('山', 101)],
                ['name' => str_repeat('山', 100)],
            ],
            'when permission is empty' => [
                ['permissions' => ['入力してください。']],
                ['permissions' => []],
                ['permissions' => [Permission::viewStaffs()->value() => true]],
            ],
            'when permission does not exist' => [
                ['permissions' => ['権限を指定してください。']],
                ['permissions' => ['not_existing_code' => true]],
                ['permissions' => [Permission::viewStaffs()->value() => true]],
            ],
            'when staff does not have permission' => [
                ['permissions' => ['権限を持っていません。']],
                ['permissions' => [Permission::updateStaffs()->value() => true]],
                ['permissions' => [Permission::viewStaffs()->value() => true]],
            ],
            'when isSystemAdmin is empty' => [
                ['isSystemAdmin' => ['入力してください。']],
                ['isSystemAdmin' => ''],
                ['isSystemAdmin' => true],
            ],
            'when invalid isSystemAdmin given' => [
                ['isSystemAdmin' => ['trueかfalseにしてください。']],
                ['isSystemAdmin' => 'aaaa'],
                ['isSystemAdmin' => true],
            ],
            'when the RoleScope parameter does not exist in the request' => [
                ['scope' => ['入力してください。']],
                ['scope' => ''],
                ['scope' => RoleScope::person()->value()],
            ],
            'when the RoleScope parameter is invalid value' => [
                ['scope' => ['権限範囲を指定してください。']],
                ['scope' => self::INVALID_ENUM_VALUE],
                ['scope' => RoleScope::person()->value()],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'name' => 'スタッフ参照ロール',
            'isSystemAdmin' => false,
            'permissions' => [Permission::viewStaffs()->value() => true],
            'scope' => RoleScope::whole()->value(),
        ];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'name' => $input['name'],
            'isSystemAdmin' => $input['isSystemAdmin'],
            'scope' => RoleScope::from($input['scope']),
            'permissions' => $input['isSystemAdmin'] ? [] : Map::from($input['permissions'])
                ->filter(fn (bool $x, string $key): bool => $x)
                ->keys()
                ->map(fn (string $x): Permission => Permission::from($x))
                ->toArray(),
        ];
    }
}
