<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\CarbonRange;
use Domain\Context\Context;
use UseCase\Calling\SendThirdCallingUseCase;

/**
 * SendThirdCalling Job.
 */
class SendThirdCallingJob extends Job
{
    private Context $context;
    private CarbonRange $range;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\CarbonRange $range
     */
    public function __construct(Context $context, CarbonRange $range)
    {
        $this->context = $context;
        $this->range = $range;
    }

    /**
     * 出勤確認第三呼び出しを行う.
     *
     * @param \UseCase\Calling\SendThirdCallingUseCase $useCase
     */
    public function handle(SendThirdCallingUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->range);
    }
}
