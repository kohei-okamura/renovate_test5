<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use Illuminate\Validation\Validator;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * FormRequest のテスト.
 */
class FormRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private FormRequestFixture $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FormRequestTest $self): void {
            $self->request = new FormRequestFixture();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_createValidatorInstance(): void
    {
        $this->should('return a Validator', function (): void {
            $this->assertInstanceOf(
                Validator::class,
                $this->request->createValidatorInstance([])
            );
        });
        $this->should('return a failed validator when invalid input given', function (): void {
            $input = ['input_key' => 'aaaaaa'];
            $validator = $this->request->createValidatorInstance($input);

            $this->assertTrue($validator->fails());
        });
        $this->should('use defined attributes and messages on creating validator', function (): void {
            $input = ['input_key' => ''];
            $validator = $this->request->createValidatorInstance($input);

            $this->assertTrue($validator->fails());
            $this->assertSame(
                ['input_key' => ['xxx入力値 が入力されていません。xxx']],
                $validator->errors()->toArray()
            );
        });
        $this->should('return a passed validator when valid input given', function (): void {
            $input = ['input_key' => 'sample@example.com'];
            $validator = $this->request->createValidatorInstance($input);

            $this->assertTrue($validator->passes());
        });
    }
}
