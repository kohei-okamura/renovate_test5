<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateWithdrawalTransactionFileRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateWithdrawalTransactionFileRequest} のテスト.
 */
final class CreateWithdrawalTransactionFileRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupWithdrawalTransactionUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    protected CreateWithdrawalTransactionFileRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new CreateWithdrawalTransactionFileRequest();

            $self->lookupWithdrawalTransactionUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->withdrawalTransactions[0]))
                ->byDefault();
            $self->lookupWithdrawalTransactionUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::downloadWithdrawalTransactions(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

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
            $this->assertTrue($validator->passes(), $validator->errors()->toJson());
        });
        $examples = [
            'when id is empty' => [
                ['id' => ['入力してください。']],
                ['id' => ''],
                ['id' => $this->examples->withdrawalTransactions[0]->id],
            ],
            'when unknown withdrawalTransactionId given' => [
                ['id' => ['正しい値を入力してください。']],
                ['id' => self::NOT_EXISTING_ID],
                ['id' => $this->examples->withdrawalTransactions[0]->id],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($input);
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
        return ['id' => $this->examples->withdrawalTransactions[0]->id];
    }
}
