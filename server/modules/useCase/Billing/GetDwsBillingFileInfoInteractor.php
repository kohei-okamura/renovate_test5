<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\File\FileStorage;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：ファイル取得 ユースケース実装.
 */
class GetDwsBillingFileInfoInteractor implements GetDwsBillingFileInfoUseCase
{
    private const TEMPORARY_FILE_EXPIRED_MINUTES = 10;

    private LookupDwsBillingUseCase $lookupUseCase;
    private FileStorage $storage;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupUseCase
     * @param \Domain\File\FileStorage $storage
     */
    public function __construct(LookupDwsBillingUseCase $lookupUseCase, FileStorage $storage)
    {
        $this->lookupUseCase = $lookupUseCase;
        $this->storage = $storage;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $billingId, string $token): string
    {
        $billingFile = $this->findBillingFileByToken($context, $billingId, $token);

        return $this->storage->getTemporaryUrl(
            $billingFile->path,
            Carbon::parse(Carbon::now()->addMinutes(self::TEMPORARY_FILE_EXPIRED_MINUTES)),
            $billingFile->name
        );
    }

    /**
     * 障害福祉サービス：請求の取得.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @return \Domain\Billing\DwsBilling
     */
    private function lookupBilling(Context $context, int $billingId): DwsBilling
    {
        return $this->lookupUseCase
            ->handle($context, Permission::viewBillings(), $billingId)
            ->headOption()
            ->getOrElse(function () use ($billingId): void {
                throw new NotFoundException("DwsBilling({$billingId}) not found");
            });
    }

    /**
     * トークンからファイルを探す.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param string $token
     * @return \Domain\Billing\DwsBillingFile
     */
    private function findBillingFileByToken(Context $context, int $billingId, string $token): DwsBillingFile
    {
        $billing = $this->lookupBilling($context, $billingId);

        return Seq::fromArray($billing->files)
            ->find(fn (DwsBillingFile $x): bool => $x->token === $token)
            ->getOrElse(function () use ($token): void {
                throw new NotFoundException("DwsBillingFile({$token}) not found");
            });
    }
}
