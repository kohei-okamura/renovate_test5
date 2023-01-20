<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\PhoneNumberRule} のテスト.
 */
final class PhoneNumberRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validatePhoneNumber(): void
    {
        $this->should(
            'pass when valid phone number given',
            function (string $validNumber): void {
                $this->assertTrue($this->phoneNumberValidator($validNumber)->passes(), $validNumber);
            },
            ['examples' => $this->validNumbers()]
        );
        $this->should(
            'fail when invalid phone number given',
            function (string $invalidNumber): void {
                $this->assertTrue($this->phoneNumberValidator($invalidNumber)->fails(), $invalidNumber);
            },
            ['examples' => $this->invalidNumbers()]
        );
    }

    /**
     * 無効な番号.
     *
     * @return array|string[]
     */
    private function invalidNumbers(): array
    {
        return [
            '1#-####-####' => ['11-2345-6789'],
            '00-####-####' => ['00-2345-6789'],
            '0#-0###-####' => ['01-0345-6789'],
            '000-###-####' => ['000-345-6789'],
            '000#-##-####' => ['0003-45-6789'],
            '000##-#-####' => ['00034-5-6789'],
            '000-####-####' => ['000-1234-5678'],
            '010-####-####' => ['010-1234-5678'],
            '020-####-####' => ['020-1234-5678'],
            '030-####-####' => ['030-1234-5678'],
            '040-####-####' => ['040-1234-5678'],
            '060-####-####' => ['060-1234-5678'],
            '0##-0##-####' => ['012-045-6789'],
            '0###-0#-####' => ['0123-05-6789'],
            '0###-0##-####' => ['0123-056-789'],
            '0####-0-####' => ['01234-0-6789'],
        ];
    }

    /**
     * 有効な番号.
     *
     * @return array|string[]
     */
    private function validNumbers(): array
    {
        return [
            '0#-####-####' => ['01-2345-6789'],
            '0#-#000-####' => ['01-2000-6789'],
            '0##-###-####' => ['012-345-6789'],
            '0##-#00-####' => ['012-300-6789'],
            '0###-##-####' => ['0123-45-6789'],
            '0#00-#0-####' => ['0100-40-6789'],
            '0####-#-####' => ['01234-5-6789'],
            '0#-####-0000' => ['01-2345-0000'],
            '0#####-####' => ['012345-6789'],
            '0#########' => ['0123456789'],
            '0##0-###-###' => ['0120-123-456'],
            '0#00-#00-00#' => ['0020-100-001'],
            '00#0-#00-0#0' => ['0100-100-010'],
            '0##0-##-####' => ['0120-12-3456'],
            '0###-#0-000#' => ['0123-10-0001'],
            '0###-#0-00#0' => ['0123-10-0010'],
            '0##0########' => ['0120123456'],
            '050-####-####' => ['050-1234-5678'],
            '070-####-####' => ['070-1234-5678'],
            '080-####-####' => ['080-1234-5678'],
            '090-####-####' => ['090-1234-5678'],
            '090########' => ['09012345678'],
        ];
    }

    /**
     * 電話番号検証用のバリデータを生成する.
     *
     * @param string $number
     * @return CustomValidator
     */
    private function phoneNumberValidator(string $number): CustomValidator
    {
        return CustomValidator::make(
            $this->context,
            ['tel' => $number],
            ['tel' => 'phone_number'],
            [],
            []
        );
    }
}
