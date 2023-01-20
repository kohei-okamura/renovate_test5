<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\DeleteUserBillingDepositRequest;
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
 * {@link \App\Http\Requests\DeleteUserBillingDepositRequest} のテスト.
 */
class DeleteUserBillingDepositRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected DeleteUserBillingDepositRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteUserBillingDepositRequestTest $self): void {
            $self->request = new DeleteUserBillingDepositRequest();
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), $self->examples->userBillings[3]->id)
                ->andReturn(Seq::empty());
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateUserBillings(), $self->examples->userBillings[2]->id)
                ->andReturn(Seq::from($self->examples->userBillings[0]->copy([
                    'depositedAt' => null,
                ])));
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
            'when ids is not empty' => [
                ['ids' => ['配列にしてください。', '正しい値を入力してください。']],
                ['ids' => 'error'],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain not existing id of UserBillings' => [
                ['ids' => ['正しい値を入力してください。']],
                ['ids' => [$this->examples->userBillings[3]->id]],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain id of UserBillings that cannot be deleted' => [
                ['ids' => ['入金日を削除できないIDが含まれています。']],
                ['ids' => [$this->examples->userBillings[2]->id]],
                ['ids' => [$this->examples->userBillings[0]->id]],
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
        ];
    }
}
