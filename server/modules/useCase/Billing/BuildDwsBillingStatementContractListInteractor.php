<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatementContract;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement as Agreement;
use Domain\Office\Office;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：明細書：契約一覧組み立てユースケース実装.
 */
final class BuildDwsBillingStatementContractListInteractor implements BuildDwsBillingStatementContractListUseCase
{
    /** {@inheritdoc} */
    public function handle(Context $context, Office $office, DwsCertification $certification, Carbon $providedIn): Seq
    {
        // 各サービス内容ごとにサービス提供年月における最新の契約内容（最終的な契約内容）のみを送信する
        // 詳細はインターフェース仕様書（事業所編）41ページ目（P.31）辺りを参照
        $endOfMonth = $providedIn->endOfMonth();
        return Seq::from(...$certification->agreements)
            ->filter(fn (Agreement $x): bool => $x->agreedOn <= $endOfMonth && $x->officeId === $office->id)
            ->groupBy(fn (Agreement $x): int => $x->dwsCertificationAgreementType->value())
            ->mapValues(fn (Seq $xs): Agreement => $xs->maxBy(fn (Agreement $x): Carbon => $x->agreedOn))
            ->values()
            ->map(fn (Agreement $x): DwsBillingStatementContract => DwsBillingStatementContract::from($x));
    }
}
