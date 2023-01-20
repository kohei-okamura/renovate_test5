<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Organization\OrganizationSetting;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingRepository;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\WithdrawalTransactionRepository;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Domain\Validator\CreateWithdrawalTransactionAsyncValidator;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Organization\LookupOrganizationSettingUseCase;

/**
 * {@link \UseCase\UserBilling\CreateWithdrawalTransactionUseCase} の実装.
 */
final class CreateWithdrawalTransactionInteractor implements CreateWithdrawalTransactionUseCase
{
    use Logging;

    private FindUserBillingUseCase $findUserBillingUseCase;
    private LookupUserBillingUseCase $lookupUserBillingUseCase;
    private LookupOrganizationSettingUseCase $lookupOrganizationSettingUseCase;
    private WithdrawalTransactionRepository $repository;
    private UserBillingRepository $userBillingRepository;
    private TransactionManager $transaction;
    private CreateWithdrawalTransactionAsyncValidator $validator;

    /**
     * Constructor.
     *
     * @param \UseCase\UserBilling\FindUserBillingUseCase $findUserBillingUseCase
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase
     * @param \UseCase\Organization\LookupOrganizationSettingUseCase $lookupOrganizationSettingUseCase
     * @param \Domain\UserBilling\WithdrawalTransactionRepository $repository
     * @param \Domain\UserBilling\UserBillingRepository $userBillingRepository
     * @param \Domain\TransactionManagerFactory $factory
     * @param CreateWithdrawalTransactionAsyncValidator $validator
     */
    public function __construct(
        FindUserBillingUseCase $findUserBillingUseCase,
        LookupUserBillingUseCase $lookupUserBillingUseCase,
        LookupOrganizationSettingUseCase $lookupOrganizationSettingUseCase,
        WithdrawalTransactionRepository $repository,
        UserBillingRepository $userBillingRepository,
        TransactionManagerFactory $factory,
        CreateWithdrawalTransactionAsyncValidator $validator
    ) {
        $this->findUserBillingUseCase = $findUserBillingUseCase;
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
        $this->lookupOrganizationSettingUseCase = $lookupOrganizationSettingUseCase;
        $this->repository = $repository;
        $this->userBillingRepository = $userBillingRepository;
        $this->transaction = $factory->factory($repository, $userBillingRepository);
        $this->validator = $validator;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $userBillingIds): WithdrawalTransaction
    {
        $this->validator->validate($context, compact('userBillingIds'));

        $x = $this->transaction->run(function () use ($context, $userBillingIds): WithdrawalTransaction {
            $userBillings = $this->lookupUserBillingUseCase
                ->handle($context, Permission::createWithdrawalTransactions(), ...$userBillingIds);
            if ($userBillings->count() !== count($userBillingIds)) {
                $ids = implode(',', $userBillingIds);
                throw new NotFoundException("UserBillings({$ids}) not found");
            }

            $organizationSetting = $this->lookupOrganizationSettingUseCase
                ->handle($context, Permission::createWithdrawalTransactions())
                ->getOrElse(function (): void {
                    throw new NotFoundException('OrganizationSetting Not Found');
                });

            $userBillingMap = $userBillings->groupBy(fn (UserBilling $x): string => $x->user->billingDestination->contractNumber);

            $result = $this->createWithdrawalTransaction($context, $userBillingMap, $organizationSetting);

            // 口座振替データの登録が成功したら対象の利用者請求の請求結果を「処理中」に更新する
            $userBillings->each(fn ($x): UserBilling => $this->userBillingRepository->store($x->copy([
                'result' => UserBillingResult::inProgress(),
                'updatedAt' => Carbon::now(),
            ])));

            return $result;
        });
        $this->logger()->info(
            '口座振替データが登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 口座振替データを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \ScalikePHP\Map $userBillingMap key=契約者番号 value=Seq|UserBilling[]
     * @param \Domain\Organization\OrganizationSetting $organizationSetting
     * @return \Domain\UserBilling\WithdrawalTransaction
     */
    private function createWithdrawalTransaction(Context $context, Map $userBillingMap, OrganizationSetting $organizationSetting): WithdrawalTransaction
    {
        $withdrawalTransaction = WithdrawalTransaction::create([
            'organizationId' => $context->organization->id,
            'items' => $userBillingMap
                ->mapValues(
                    function (Seq $userBillings) use ($organizationSetting, $context): Option {
                        $userBilling = $userBillings->headOption()->getOrElse(function (): void {
                            throw new LogicException('UserBillings cannot be empty');
                        });
                        assert($userBilling instanceof UserBilling);

                        return $userBillings->map(fn (UserBilling $x): int => $x->totalAmount)->sum() <= 0
                            ? Option::none()
                            : Option::some(WithdrawalTransactionItem::create([
                                'userBillingIds' => $userBillings->map(fn (UserBilling $x): int => $x->id)->toArray(),
                                'zenginRecord' => ZenginDataRecord::from(
                                    $userBillings,
                                    $organizationSetting,
                                    $this->resolveDataRecordCode($context, $userBilling)
                                ),
                            ]));
                    }
                )
                ->values()
                ->flatten()
                ->toArray(),
            // 全ての利用者請求の口座振替日が同一である前提で先頭の口座振替日を設定
            'deductedOn' => $userBillingMap
                ->values()
                ->flatten()
                ->headOption()
                ->getOrElse(function (): void {
                    throw new LogicException('UserBillings cannot be empty');
                })
                ->deductedOn,
            'downloadedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
        return $this->repository->store($withdrawalTransaction);
    }

    /**
     * 全銀レコード：データレコード：新規コードを導出する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\UserBilling\UserBilling $userBilling
     * @return \Domain\UserBilling\ZenginDataRecordCode
     */
    private function resolveDataRecordCode(Context $context, UserBilling $userBilling): ZenginDataRecordCode
    {
        $previousUserBillingOption = $this->findUserBillingUseCase
            ->handle(
                $context,
                Permission::createWithdrawalTransactions(),
                [
                    'contractNumber' => $userBilling->user->billingDestination->contractNumber,
                    'withdrawalResultCode' => WithdrawalResultCode::done()->value(),
                ],
                [
                    'all' => true,
                    'sortBy' => 'date',
                    'desc' => true,
                ]
            )
            ->list
            ->headOption();

        // 請求が初めての場合は初回
        if ($previousUserBillingOption->isEmpty()) {
            return ZenginDataRecordCode::firstTime();
        }

        $previousUserBilling = $previousUserBillingOption->get();
        assert($previousUserBilling instanceof UserBilling);

        // 前回と口座が同じ場合はその他、異なる場合は初回
        return $userBilling->user->bankAccount->isSameBankAccount($previousUserBilling->user->bankAccount)
            ? ZenginDataRecordCode::other()
            : ZenginDataRecordCode::firstTime();
    }
}
