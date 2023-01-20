<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Shift\LookupShiftUseCase;

/**
 * スタッフ出勤勤務シフト取得実装.
 */
final class GetShiftsByTokenInteractor implements GetShiftsByTokenUseCase
{
    use Logging;

    private LookupCallingByTokenUseCase $lookupCallingByTokenUseCase;
    private LookupShiftUseCase $lookupShiftUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Calling\LookupCallingByTokenUseCase $lookupCallingByTokenUseCase
     * @param \UseCase\Shift\LookupShiftUseCase $lookupShiftUseCase
     */
    public function __construct(
        LookupCallingByTokenUseCase $lookupCallingByTokenUseCase,
        LookupShiftUseCase $lookupShiftUseCase
    ) {
        $this->lookupCallingByTokenUseCase = $lookupCallingByTokenUseCase;
        $this->lookupShiftUseCase = $lookupShiftUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): FinderResult
    {
        $calling = $this->lookupCallingByTokenUseCase
            ->handle($context, $token)
            ->getOrElse(function () use ($token): void {
                throw new NotFoundException("Calling[{$token}] not found");
            });
        $shifts = $this->lookupShiftUseCase
            ->handle($context, Permission::listShifts(), ...$calling->shiftIds)
            ->sortBy(fn (Shift $x): Carbon => $x->schedule->start);
        if ($shifts->isEmpty()) {
            throw new NotFoundException("Shift is empty. CallingID={$calling->id}");
        }

        return FinderResult::from($shifts, Pagination::create([
            'count' => $shifts->count(),
            'desc' => false,
            'itemsPerPage' => $shifts->count(),
            'page' => 1,
            'pages' => 1,
            'sortBy' => 'date',
        ]));
    }
}
