<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\StaffBankAccountController;
use App\Http\Requests\UpdateStaffBankAccountRequest;
use Domain\BankAccount\BankAccountType;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EditStaffBankAccountUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * StaffBankAccountControllerのテスト。
 */
class StaffBankAccountControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EditStaffBankAccountUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private StaffBankAccountController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffBankAccountControllerTest $self): void {
            $self->editStaffBankAccountUseCase
                ->allows('handle')
                ->andReturn($self->examples->bankAccounts[0])
                ->byDefault();
            $self->controller = app(StaffBankAccountController::class);
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staff/{staffId}/bank-account',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateStaffBankAccountRequest::class, function () {
            $request = Mockery::mock(UpdateStaffBankAccountRequest::class)->makePartial();
            $request->allows('context')
                ->andReturn($this->context)
                ->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    [
                        'staffId' => $this->examples->staffs[0]->id,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a response of entity', function (): void {
            $bankAccount = $this->examples->bankAccounts[0];

            $response = app()->call(
                [$this->controller, 'update'],
                [
                    'staffId' => $this->examples->staffs[0]->id,
                ]
            );

            $this->assertSame(Json::encode(compact('bankAccount'), 0), $response->getContent());
        });
        $this->should('update StaffBankAccount using use case', function (): void {
            $this->editStaffBankAccountUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->staffs[0]->id,
                    equalTo($this->payload())
                )
                ->andReturn($this->examples->bankAccounts[0]);

            app()->call(
                [$this->controller, 'update'],
                ['staffId' => $this->examples->staffs[0]->id]
            );
        });
    }

    /**
     * Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'bankName' => 'ユースタイル銀行',
            'bankCode' => '0123',
            'bankBranchName' => '中野ハーモニータワー支店',
            'bankBranchCode' => '456',
            'bankAccountType' => BankAccountType::ordinaryDeposit()->value(),
            'bankAccountNumber' => '0123456',
            'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
        ];
    }

    /**
     * payload が返す配列を作成する.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->input();
        return ['bankAccountType' => BankAccountType::from($input['bankAccountType'])] + $input;
    }
}
