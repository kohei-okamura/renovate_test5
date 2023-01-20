<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountType;
use Domain\Model;
use Domain\Organization\OrganizationSetting;
use Lib\Exceptions\LogicException;
use Lib\KanaConverter;
use ScalikePHP\Seq;

/**
 * 全銀レコード：データレコード.
 *
 * @property-read string $bankCode 引落銀行番号
 * @property-read string $bankBranchCode 引落支店番号
 * @property-read \Domain\BankAccount\BankAccountType $bankAccountType 預金種目
 * @property-read string $bankAccountNumber 口座番号
 * @property-read string $bankAccountHolder 預金者名
 * @property-read int $amount 引落金額
 * @property-read \Domain\UserBilling\ZenginDataRecordCode $dataRecordCode 新規コード
 * @property-read string $clientNumber 顧客番号
 * @property-read \Domain\UserBilling\WithdrawalResultCode $withdrawalResultCode 振替結果コード
 */
final class ZenginDataRecord extends Model
{
    use ZenginRecordSupport;

    private const FORMAT = [
        // データ区分
        'dataType' => 1,
        // 引落銀行番号
        'bankCode' => 4,
        // 引落銀行名
        'bankName' => 15,
        // 引落支店番号
        'bankBranchCode' => 3,
        // 引落支店名
        'bankBranchName' => 15,
        // ダミー
        'dummy1' => 4,
        // 預金種目
        'bankAccountType' => 1,
        // 口座番号
        'bankAccountNumber' => 7,
        // 預金者名
        'bankAccountHolder' => 30,
        // 引落金額
        'amount' => 10,
        // 新規コード
        'dataRecordCode' => 1,
        // 顧客番号
        'clientNumber' => 20,
        // 振替結果コード
        'withdrawalResultCode' => 1,
        // ダミー2
        'dummy2' => 8,
    ];

    /**
     * 全銀レコード：データレコードを生成する.
     *
     * @param \Domain\UserBilling\UserBilling|\ScalikePHP\Seq $userBillings
     * @param \Domain\Organization\OrganizationSetting $organizationSetting
     * @param \Domain\UserBilling\ZenginDataRecordCode $zenginDataRecordCode
     * @return \Domain\UserBilling\ZenginDataRecord
     */
    public static function from(
        Seq $userBillings,
        OrganizationSetting $organizationSetting,
        ZenginDataRecordCode $zenginDataRecordCode
    ): self {
        /** @var \Domain\UserBilling\UserBilling $userBilling */
        $userBilling = $userBillings->headOption()->getOrElse(function (): void {
            throw new LogicException('ZenginDataRecord cannot be created when UserBilling is an empty');
        });
        $bankAccount = $userBilling->user->bankAccount;
        $halfWidthKatakanaBankAccountHolder = KanaConverter::toUppercaseHalfWidthKatakana($bankAccount->bankAccountHolder);
        return self::create([
            'bankCode' => $bankAccount->bankCode,
            'bankBranchCode' => $bankAccount->bankBranchCode,
            'bankAccountType' => $bankAccount->bankAccountType,
            'bankAccountNumber' => self::adjustBankAccountNumber($bankAccount),
            'bankAccountHolder' => str_replace('￥', '¥', $halfWidthKatakanaBankAccountHolder),
            'amount' => $userBillings->map(fn (UserBilling $x): int => $x->totalAmount)->sum(),
            'dataRecordCode' => $zenginDataRecordCode,
            'clientNumber' => $organizationSetting->bankingClientCode . $userBilling->user->billingDestination->contractNumber,
            'withdrawalResultCode' => WithdrawalResultCode::done(),
        ]);
    }

    /**
     * レコード文字列からインスタンスを生成する.
     *
     * @param string $recordString
     * @return static
     */
    public static function parse(string $recordString): self
    {
        $attrs = self::parseRecordString($recordString, self::FORMAT);

        self::ensureNumeric($attrs['amount']);
        self::ensureNumeric($attrs['clientNumber']);

        // 全銀ファイルで「9:その他」の場合はシステムに該当する値がないので不明としておく.
        // この値を利用する予定はないので問題はない.
        $bankAccountType = (int)$attrs['bankAccountType'] === 9
            ? BankAccountType::unknown()
            : BankAccountType::from((int)$attrs['bankAccountType']);

        return self::create([
            'bankCode' => $attrs['bankCode'],
            'bankBranchCode' => $attrs['bankBranchCode'],
            'bankAccountType' => $bankAccountType,
            'bankAccountNumber' => $attrs['bankAccountNumber'],
            'bankAccountHolder' => $attrs['bankAccountHolder'],
            'amount' => (int)$attrs['amount'],
            'dataRecordCode' => ZenginDataRecordCode::from((int)$attrs['dataRecordCode']),
            'clientNumber' => $attrs['clientNumber'],
            'withdrawalResultCode' => WithdrawalResultCode::from((int)$attrs['withdrawalResultCode']),
        ]);
    }

    /**
     * 全銀レコード文字列（UTF-8）に変換する.
     *
     * @return string
     */
    public function toRecordString(): string
    {
        $values = [
            // データ区分
            '2',
            // 引落銀行番号
            self::pad($this->bankCode, self::FORMAT['bankCode'], '0', \STR_PAD_LEFT),
            // 引落銀行名
            str_repeat(' ', self::FORMAT['bankName']),
            // 引落支店番号
            self::pad($this->bankBranchCode, self::FORMAT['bankBranchCode'], '0', \STR_PAD_LEFT),
            // 引落支店名
            str_repeat(' ', self::FORMAT['bankBranchName']),
            // ダミー
            str_repeat(' ', self::FORMAT['dummy1']),
            // 預金種目
            $this->resolveBankAccountType(),
            // 口座番号
            self::pad($this->bankAccountNumber, self::FORMAT['bankAccountType'], '0', \STR_PAD_LEFT),
            // 預金者名
            self::pad($this->bankAccountHolder, self::FORMAT['bankAccountHolder'], ' ', \STR_PAD_RIGHT),
            // 引落金額
            self::pad((string)$this->amount, self::FORMAT['amount'], '0', \STR_PAD_LEFT),
            // 新規コード
            $this->dataRecordCode->value(),
            // 顧客番号
            self::pad($this->clientNumber, self::FORMAT['clientNumber'], '0', \STR_PAD_LEFT),
            // 振替結果コード
            $this->withdrawalResultCode->value(),
            // ダミー
            str_repeat(' ', self::FORMAT['dummy2']),
        ];
        return implode('', $values);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'bankCode',
            'bankBranchCode',
            'bankAccountType',
            'bankAccountNumber',
            'bankAccountHolder',
            'amount',
            'dataRecordCode',
            'clientNumber',
            'withdrawalResultCode',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'bankCode' => true,
            'bankBranchCode' => true,
            'bankAccountType' => true,
            'bankAccountNumber' => true,
            'bankAccountHolder' => true,
            'amount' => true,
            'dataRecordCode' => true,
            'clientNumber' => true,
            'withdrawalResultCode' => true,
        ];
    }

    /**
     * 銀行口座：種別から預金種目を導出する.
     *
     * @return int
     */
    private function resolveBankAccountType(): int
    {
        switch ($this->bankAccountType) {
            case BankAccountType::ordinaryDeposit():
                return 1; // 普通預金
            case BankAccountType::currentDeposit():
                return 2; // 当座預金
            default:
                return 9; // その他
        }
    }

    /**
     * 銀行口座：口座番号を全銀ファイルフォーマットに合うように調整する.
     *
     * @param \Domain\UserBilling\UserBillingBankAccount $bankAccount
     * @throws \Lib\Exceptions\LogicException
     * @return string
     */
    private static function adjustBankAccountNumber(UserBillingBankAccount $bankAccount): string
    {
        $original = $bankAccount->bankAccountNumber;
        // ゆうちょ銀行以外の場合はそのまま返す
        if ($bankAccount->bankCode !== BankAccount::JAPAN_POST_BANK_BANK_CODE) {
            return $original;
        }
        // ゆうちょ銀行で 8 桁、かつ末尾が 1 の場合、末尾の 1 は不要なので取り除く
        $adjusted = strlen($original) === 8 && str_ends_with($original, '1')
            ? substr($original, 0, -1)
            : $original;

        if (strlen($adjusted) >= 8) {
            throw new LogicException("The bank account number must be less than 8 digits. {$adjusted} is invalid.");
        }

        return $adjusted;
    }
}
