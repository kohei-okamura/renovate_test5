<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginRecord;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\ValidationException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;

/**
 * {@link \UseCase\UserBilling\ResolveUserBillingsFromZenginFormatUseCase} の実装.
 */
final class ResolveUserBillingsFromZenginFormatInteractor implements ResolveUserBillingsFromZenginFormatUseCase
{
    private ParseZenginFormatUseCase $parseZenginFormatUseCase;
    private FindWithdrawalTransactionUseCase $findWithdrawalTransactionUseCase;
    private LookupUserBillingUseCase $lookupUserBillingUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\UserBilling\ParseZenginFormatUseCase $parseZenginFormatUseCase
     * @param \UseCase\UserBilling\FindWithdrawalTransactionUseCase $findWithdrawalTransactionUseCase
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase
     */
    public function __construct(
        ParseZenginFormatUseCase $parseZenginFormatUseCase,
        FindWithdrawalTransactionUseCase $findWithdrawalTransactionUseCase,
        LookupUserBillingUseCase $lookupUserBillingUseCase
    ) {
        $this->parseZenginFormatUseCase = $parseZenginFormatUseCase;
        $this->findWithdrawalTransactionUseCase = $findWithdrawalTransactionUseCase;
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, SplFileInfo $file): Map
    {
        try {
            $zenginRecord = $this->parseZenginFormatUseCase->handle($context, $file);
        } catch (InvalidArgumentException $exception) {
            throw new ValidationException(Seq::from('全銀ファイルではありません。'));
        }
        $this->validateDeductedOnWithin6Months($zenginRecord);
        $this->validateWithdrawalResultIsValid($zenginRecord);

        $withdrawalTransactionItems = $this->findWithdrawalTransactionUseCase
            ->handle(
                $context,
                Permission::downloadWithdrawalTransactions(),
                ['deductedOn' => $zenginRecord->header->deductedOn],
                ['all' => true]
            )
            ->list
            ->flatMap(fn (WithdrawalTransaction $x): array => $x->items);

        // リポジトリから取ってきた「口座振替データ：明細」の中に該当する全銀データがなければバリデーションエラー
        $this->validateWithdrawalTransactionItemsExist($withdrawalTransactionItems, $zenginRecord);

        // 顧客番号と引落金額で絞り込み、振替結果コードを置き換える
        // これだと、同月に他の事業所で全く同じ請求金額でサービス受けてた場合に利用者請求が特定できないが、
        // そこまでは論理的に不可能であるためこのようになっている
        $filteredWithdrawalTransactionItems = $withdrawalTransactionItems
            ->flatMap(
                fn (WithdrawalTransactionItem $x): Option => Seq::from(...$zenginRecord->data)
                    ->find(
                        fn (ZenginDataRecord $data): bool => $data->clientNumber === $x->zenginRecord->clientNumber
                            && $data->amount === $x->zenginRecord->amount
                    )
                    ->map(fn (ZenginDataRecord $data): WithdrawalTransactionItem => $x->copy([
                        'zenginRecord' => $x->zenginRecord->copy([
                            'withdrawalResultCode' => $data->withdrawalResultCode,
                        ]),
                    ]))
            );

        // このままでは上記の通り、同月に他の事業所で全く同じ請求金額でサービス受けてた場合に
        // それら全ての利用者請求が対象になってしまうため、その中の1つだけが対象となるように調整する
        $targetItems = $this->takeNoOverlappedWithdrawalTransactionItems($filteredWithdrawalTransactionItems)
            ->append($this->takeOverlappedWithdrawalTransactionItems($context, $filteredWithdrawalTransactionItems));

        $this->validateAllUserBillingsAreInProgress($context, $targetItems->flatMap(fn (WithdrawalTransactionItem $x) => $x->userBillingIds));

        return Map::from(call_user_func(function () use ($targetItems, $zenginRecord): iterable {
            foreach ($targetItems as $item) {
                foreach ($item->userBillingIds as $id) {
                    yield $id => [$item->zenginRecord->withdrawalResultCode, $zenginRecord->header->deductedOn];
                }
            }
        }));
    }

    /**
     * 該当する「口座振替データ：明細」が存在するかバリデーションする.
     *
     * @param \Domain\UserBilling\WithdrawalTransactionItem[]|\ScalikePHP\Seq $withdrawalTransactionItems
     * @param \Domain\UserBilling\ZenginRecord $zenginRecord
     */
    private function validateWithdrawalTransactionItemsExist(Seq $withdrawalTransactionItems, ZenginRecord $zenginRecord): void
    {
        $passed = Seq::from(...$zenginRecord->data)
            ->forAll(
                fn (ZenginDataRecord $x): bool => $withdrawalTransactionItems
                    ->exists(
                        fn (WithdrawalTransactionItem $item): bool => $item->zenginRecord->clientNumber === $x->clientNumber
                            && $item->zenginRecord->amount === $x->amount
                    )
            );
        if (!$passed) {
            throw new ValidationException(Seq::from('該当するデータがありません。'));
        }
    }

    /**
     * 「引落金額」かつ「顧客番号」が重複しないものを取り出す.
     *
     * @param \ScalikePHP\Seq $withdrawalTransactionItems
     * @return \ScalikePHP\Seq
     */
    private function takeNoOverlappedWithdrawalTransactionItems(Seq $withdrawalTransactionItems): Seq
    {
        return $withdrawalTransactionItems->filter(
            fn (WithdrawalTransactionItem $x) => $withdrawalTransactionItems->filter(
                fn (WithdrawalTransactionItem $y) => $x->zenginRecord->clientNumber === $y->zenginRecord->clientNumber
                        && $x->zenginRecord->amount === $y->zenginRecord->amount
            )->count() === 1
        );
    }

    /**
     * 「引落金額」と「顧客番号」の両方が一致し、かつ「請求結果」が「処理中」であるものがあれば取り出す.
     *
     * @param \Domain\Context\Context $context
     * @param \ScalikePHP\Seq $withdrawalTransactionItems
     * @return \Domain\UserBilling\WithdrawalTransactionItem[]|\ScalikePHP\Seq
     */
    private function takeOverlappedWithdrawalTransactionItems(Context $context, Seq $withdrawalTransactionItems): Seq
    {
        $clientNumberToItemsMap = $withdrawalTransactionItems
            // amount と clientNumber の両方が一致するものだけを選択
            ->filter(
                fn (WithdrawalTransactionItem $x) => $withdrawalTransactionItems->filter(
                    fn (WithdrawalTransactionItem $y) => $x->zenginRecord->clientNumber === $y->zenginRecord->clientNumber
                            && $x->zenginRecord->amount === $y->zenginRecord->amount
                )->count() > 1
            )
            // かなりレアなケースだが、アップロードされた全銀ファイルのデータレコードの中に
            // 同月に他の事業所で全く同じ請求金額でサービス受けてた利用者が複数いる可能性もあるため、顧客番号でグループ化
            ->groupBy(fn (WithdrawalTransactionItem $x) => $x->zenginRecord->clientNumber);

        $f = function () use ($context, $clientNumberToItemsMap) {
            foreach ($clientNumberToItemsMap as $items) {
                yield $items
                    // 「請求結果」が「処理中」であるもの（複数あれば先頭）を選択
                    ->find(function (WithdrawalTransactionItem $x) use ($context): bool {
                        /** @var \Domain\UserBilling\UserBilling $userBilling */
                        $userBilling = $this->lookupUserBillingUseCase
                            ->handle(
                                $context,
                                Permission::downloadWithdrawalTransactions(),
                                $x->userBillingIds[0] // ここのIDは複数あるうちのどれを選んでも請求結果が同一のはずなので先頭を使う
                            )
                            ->headOption()
                            ->getOrElse(function (): void {
                                throw new ValidationException(Seq::from('該当するデータがありません。'));
                            });
                        return $userBilling->result === UserBillingResult::inProgress();
                    })
                    ->getOrElse(function (): void {
                        // 「請求結果」が「処理中」である利用者請求が見つからない場合、アップロード済みファイルとみなす
                        throw new ValidationException(Seq::from('アップロード済みのファイルです。'));
                    });
            }
        };
        return Seq::fromArray($f());
    }

    /**
     * 引落日が6ヶ月以上前でないかバリデーションする.
     *
     * @param \Domain\UserBilling\ZenginRecord $zenginRecord
     */
    private function validateDeductedOnWithin6Months(ZenginRecord $zenginRecord): void
    {
        if ($zenginRecord->header->deductedOn <= Carbon::today()->subMonths(6)) {
            throw new ValidationException(Seq::from('6ヶ月以上前の全銀ファイルはアップロードできません。'));
        }
    }

    /**
     * 振替結果データが正しいかバリデーションする.
     * （「振替結果コード」が「振替済」のデータレコード件数 = 「トレーラレコード」の「振替済件数」となっているか）
     *
     * @param ZenginRecord $zenginRecord
     */
    private function validateWithdrawalResultIsValid(ZenginRecord $zenginRecord): void
    {
        $doneDataCount = Seq::from(...$zenginRecord->data)
            ->filter(fn (ZenginDataRecord $x) => $x->withdrawalResultCode === WithdrawalResultCode::done())
            ->count();
        if ($doneDataCount !== $zenginRecord->trailer->succeededCount) {
            throw new ValidationException(Seq::from('振替結果データが不正のため処理できません。'));
        }
    }

    /**
     * 全ての利用者請求の「請求結果」が「処理中」であるかバリデーションする.
     * （「処理中」でないものがある場合はアップロード済みファイルとみなす）
     *
     * @param \Domain\Context\Context $context
     * @param int[]|\ScalikePHP\Seq $ids
     */
    private function validateAllUserBillingsAreInProgress(Context $context, Seq $ids): void
    {
        $passed = $this->lookupUserBillingUseCase
            ->handle(
                $context,
                Permission::downloadWithdrawalTransactions(),
                ...$ids
            )
            ->forAll(fn (UserBilling $x): bool => $x->result === UserBillingResult::inProgress());

        if (!$passed) {
            throw new ValidationException(Seq::from('アップロード済みのファイルです。'));
        }
    }
}
