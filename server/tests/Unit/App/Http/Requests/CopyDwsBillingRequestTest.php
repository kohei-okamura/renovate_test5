<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CopyDwsBillingRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Billing\DwsBillingStatus;
use Illuminate\Support\Arr;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CopyDwsBillingRequest} のテスト.
 */
final class CopyDwsBillingRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private CopyDwsBillingRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new CopyDwsBillingRequest();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()])))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->with(Mockery::any(), Mockery::any(), $self->examples->dwsBillings[1]->id)
                ->andReturn(Seq::from($self->examples->dwsBillings[1]->copy(['status' => DwsBillingStatus::ready()])))
                ->byDefault();
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
            $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
        $examples = [
            'when id is empty' => [
                ['id' => ['入力してください。']],
                ['id' => ''],
                ['id' => $this->examples->dwsBillings[0]->id],
            ],
            'when billing cannot be copied' => [
                ['id' => ['コピーを作成できません。']],
                ['id' => $this->examples->dwsBillings[1]->id],
                ['id' => $this->examples->dwsBillings[0]->id],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $val) {
                    Arr::set($input, $key, $val);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                }
            },
            compact('examples')
        );
    }

    /**
     * 入力値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            // パスパラメータ
            'id' => $this->examples->dwsBillings[0]->id,
        ];
    }
}
