<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Calling\Calling;
use Domain\Calling\CallingResponse;
use Domain\Calling\CallingResponseRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\TokenExpiredException;
use Lib\Logging;

/**
 * スタッフ出勤確認承認実装.
 */
final class AcknowledgeStaffAttendanceInteractor implements AcknowledgeStaffAttendanceUseCase
{
    use Logging;

    private CallingResponseRepository $repository;
    private LookupCallingByTokenUseCase $lookupCallingByTokenUseCase;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Calling\CallingResponseRepository $repository
     * @param \UseCase\Calling\LookupCallingByTokenUseCase $lookupCallingByTokenUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        CallingResponseRepository $repository,
        LookupCallingByTokenUseCase $lookupCallingByTokenUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->lookupCallingByTokenUseCase = $lookupCallingByTokenUseCase;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): void
    {
        $calling = $this->lookupCallingByTokenUseCase->handle($context, $token)
            ->getOrElse(function () use ($token): void {
                throw new NotFoundException("Calling[{$token}] not found");
            });

        assert($calling instanceof Calling);
        if ($calling->expiredAt->isPast()) {
            throw new TokenExpiredException("Calling[{$token}] is expired");
        }

        $entity = CallingResponse::create([
            'callingId' => $calling->id,
            'createdAt' => Carbon::now(),
        ]);
        $x = $this->transaction->run(fn () => $this->repository->store($entity));
        $this->logger()->info(
            '出勤確認応答を登録しました',
            ['id' => $x->id] + $context->logContext()
        );
    }
}
