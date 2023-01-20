<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateUserBillingReceiptRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CreateUserBillingReceiptRequest のテスト
 */
class CreateUserBillingReceiptRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected CreateUserBillingReceiptRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingReceiptRequestTest $self): void {
            $self->request = new CreateUserBillingReceiptRequest();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::viewUserBillings(), $self->examples->userBillings[1]->id)
                ->andReturn(Seq::empty());
            // 入金日が未登録の利用者請求を返す
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::viewUserBillings(), $self->examples->userBillings[2]->id)
                ->andReturn(Seq::from($self->examples->userBillings[2]->copy(['depositedAt' => null])));
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::viewUserBillings(), $self->examples->userBillings[25]->id)
                ->andReturn(Seq::from($self->examples->userBillings[25]));
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
            'when ids are empty' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids is not array' => [
                ['ids' => ['配列にしてください。']],
                ['ids' => 1],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain not existing id of UserBillings' => [
                ['ids' => ['正しい値を入力してください。']],
                ['ids' => [$this->examples->userBillings[1]->id]],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain id of UserBilling which no deposit has been registered' => [
                ['ids' => ['入金日が未登録の利用者請求が含まれています。']],
                ['ids' => [$this->examples->userBillings[2]->id]],
                ['ids' => [$this->examples->userBillings[0]->id]],
            ],
            'when ids contain for UserBillings that do not require billing' => [
                ['ids' => ['請求なし以外の利用者請求を指定してください。']],
                ['ids' => [$this->examples->userBillings[25]->id]],
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
                $failedInput = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($failedInput, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($failedInput);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $normalInput = $this->defaultInput();
                    foreach ($valid as $key => $value) {
                        Arr::set($normalInput, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($normalInput);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
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
