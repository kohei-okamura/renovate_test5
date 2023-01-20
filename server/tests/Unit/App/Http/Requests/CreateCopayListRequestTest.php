<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateCopayListRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateCopayListRequest} のテスト.
 */
final class CreateCopayListRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use MockeryMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use UnitSupport;

    protected CreateCopayListRequest $request;

    /** @var \Domain\Billing\DwsBillingStatement[] */
    private array $billingStatements;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billingStatements = $self->examples->dwsBillingStatements;
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturns(Seq::from($self->examples->dwsBillings[0]->copy([
                    'office' => DwsBillingOffice::from($self->examples->offices[1]),
                ])))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturns(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateBillings(),
                    $self->examples->dwsBillingStatements[1]->id
                )
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[1]->copy([
                    'copayCoordination' => DwsBillingStatementCopayCoordination::create([
                        'office' => DwsBillingOffice::from($self->examples->offices[1]),
                        'result' => CopayCoordinationResult::appropriated(),
                        'amount' => 2000,
                    ]),
                ])))
                ->byDefault();
            $self->request = new CreateCopayListRequest();
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
                ['ids' => [$this->billingStatements[0]->id]],
            ],
            'when ids is not array' => [
                ['ids' => ['配列にしてください。']],
                ['ids' => 'error'],
                ['ids' => [$this->billingStatements[0]->id]],
            ],
            'when ids includes an id that can not be download copayList' => [
                ['ids' => ['利用者負担額一覧表をダウンロードできない明細書が含まれています。']],
                ['ids' => [$this->billingStatements[1]->id]],
                ['ids' => [$this->billingStatements[0]->id]],
            ],
            'when isDivided is empty' => [
                ['isDivided' => ['入力してください。']],
                ['isDivided' => []],
                ['isDivided' => true],
            ],
            'when isDivided is not bool' => [
                ['isDivided' => ['trueかfalseにしてください。']],
                ['isDivided' => 'error'],
                ['isDivided' => true],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
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
            'billingId' => $this->billingStatements[0]->dwsBillingId,
            'ids' => [$this->billingStatements[0]->id],
            'isDivided' => false,
        ];
    }
}
