<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Staff\Staff;
use Domain\Staff\StaffRememberToken;
use Domain\Staff\StaffRememberTokenRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;

/**
 * スタッフリメンバートークン作成ユースケース実装.
 */
final class CreateStaffRememberTokenInteractor implements CreateStaffRememberTokenUseCase
{
    use Logging;
    use UniqueTokenSupport;

    private const MAX_RETRY_COUNT = 100;
    private const TOKEN_LENGTH = 60;

    private Config $config;
    private StaffRememberTokenRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \Domain\Staff\StaffRememberTokenRepository $repository
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        Config $config,
        StaffRememberTokenRepository $repository,
        TokenMaker $tokenMaker,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->config = $config;
        $this->repository = $repository;
        $this->tokenMaker = $tokenMaker;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /**
     * スタッフのリメンバートークンを作成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     * @throws \Throwable
     * @return \Domain\Staff\StaffRememberToken
     */
    public function handle(Context $context, Staff $staff): StaffRememberToken
    {
        $x = $this->transaction->run(function () use ($staff) {
            $days = $this->config->get('zinger.remember_token.lifetime_days');
            $token = StaffRememberToken::create([
                'staffId' => $staff->id,
                'token' => $this->createUniqueToken(self::TOKEN_LENGTH, self::MAX_RETRY_COUNT),
                'expiredAt' => Carbon::now()->addDays($days),
                'createdAt' => Carbon::now(),
            ]);
            return $this->repository->store($token);
        });
        $this->logger()->info(
            'スタッフリメンバートークンが登録されました',
            ['id' => $x->id, 'staffId' => $staff->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * トークンがユニークかを検査する.
     *
     * @param string $token
     * @return bool
     */
    protected function isUnique(string $token): bool
    {
        return $this->repository->lookupOptionByToken($token)->isEmpty();
    }
}
