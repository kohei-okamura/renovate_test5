<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\ImportShiftRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Shift\Shift;
use Illuminate\Http\UploadedFile;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * ImportShiftRequest のテスト.
 */
class ImportShiftRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private const EXAMPLE_FILE = 'example.xlsx';

    protected ImportShiftRequest $request;
    private Shift $shift;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportShiftRequestTest $self): void {
            $self->request = new ImportShiftRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $self->shift = $self->examples->shifts[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
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
                ['file' => UploadedFile::fake()->create('example.xlsx')],
            ],
            'when file is not file' => [
                ['file' => ['ファイルを選択してください。', 'xlsxタイプのファイルを選択してください。']],
                ['file' => 'file'],
                ['file' => UploadedFile::fake()->create('example.xlsx')],
            ],
            'when file is not Excel file' => [
                ['file' => ['xlsxタイプのファイルを選択してください。']],
                ['file' => UploadedFile::fake()->create('example.csv')],
                ['file' => UploadedFile::fake()->create('example.xlsx')],
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
