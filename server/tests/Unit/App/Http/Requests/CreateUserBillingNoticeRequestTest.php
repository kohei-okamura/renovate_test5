<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateUserBillingNoticeRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateUserBillingNoticeRequest} のテスト.
 */
class CreateUserBillingNoticeRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected CreateUserBillingNoticeRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingNoticeRequestTest $self): void {
            $self->request = new CreateUserBillingNoticeRequest();
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::viewUserBillings(), $self->examples->userBillings[3]->id)
                ->andReturn(Seq::empty());
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::viewUserBillings(), $self->examples->userBillings[11]->id)
                ->andReturn(Seq::from($self->examples->userBillings[11]));
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when ids is empty' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids is not array' => [
                ['ids' => ['配列にしてください。', '正しい値を入力してください。']],
                ['ids' => 'error'],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain not existing id of UserBillings' => [
                ['ids' => ['正しい値を入力してください。']],
                ['ids' => [$this->examples->userBillings[3]->id]],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain id of UserBillings that cannot be created' => [
                ['ids' => ['代理受領額通知書を作成できない利用者請求が含まれています。']],
                ['ids' => [$this->examples->userBillings[11]->id]],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when issuedOn is empty' => [
                ['issuedOn' => ['入力してください。']],
                ['issuedOn' => ''],
                ['issuedOn' => '2021-11-10T00:00:00Z'],
            ],
            'when invalid issuedOn given' => [
                ['issuedOn' => ['正しい日付を入力してください。']],
                ['issuedOn' => '1999-02-29T00:00:00Z'],
                ['issuedOn' => '2021-11-10T00:00:00Z'],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
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
            'ids' => [$this->examples->userBillings[0]->id],
            'issuedOn' => '2021-11-10',
        ];
    }
}
