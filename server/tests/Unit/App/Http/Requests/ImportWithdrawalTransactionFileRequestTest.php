<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\ImportWithdrawalTransactionFileRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\UploadedFile;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\ImportWithdrawalTransactionFileRequest} のテスト.
 */
final class ImportWithdrawalTransactionFileRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private const EXAMPLE_FILE = 'example';

    protected ImportWithdrawalTransactionFileRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (ImportWithdrawalTransactionFileRequestTest $self): void {
            $self->request = new ImportWithdrawalTransactionFileRequest();
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
            'when file is empty' => [
                ['file' => ['入力してください。']],
                ['file' => null],
                ['file' => UploadedFile::fake()->create(self::EXAMPLE_FILE)],
            ],
            'when file is not file' => [
                ['file' => ['ファイルを選択してください。']],
                ['file' => 'file'],
                ['file' => UploadedFile::fake()->create(self::EXAMPLE_FILE)],
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
            'file' => UploadedFile::fake()->create(self::EXAMPLE_FILE),
        ];
    }
}
