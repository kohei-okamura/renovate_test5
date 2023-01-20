<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\LocationResolver;
use Domain\Context\Context;
use Domain\Office\Office;
use UseCase\Office\EditOfficeUseCase;

/**
 * 事業所位置情報更新ジョブ.
 */
final class EditOfficeLocationJob extends Job
{
    private Context $context;
    private Office $office;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     */
    public function __construct(Context $context, Office $office)
    {
        $this->context = $context;
        $this->office = $office;
    }

    /**
     * 住所情報から位置情報を取得し更新する.
     *
     * @param \Domain\Common\LocationResolver $resolver
     * @param \UseCase\Office\EditOfficeUseCase $useCase
     * @return void
     */
    public function handle(
        LocationResolver $resolver,
        EditOfficeUseCase $useCase
    ): void {
        $locationOption = $resolver->resolve($this->context, $this->office->addr);
        if ($locationOption->nonEmpty()) {
            $location = $locationOption->head();
            $useCase->handle($this->context, $this->office->id, compact('location'), function (Office $office): void {
                // 特に何もしない
            });
        }
    }
}
