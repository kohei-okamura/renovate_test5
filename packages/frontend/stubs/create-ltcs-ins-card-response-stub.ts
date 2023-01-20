/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsInsCardId } from '~/models/ltcs-ins-card'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { createLtcsInsCardStub, LTCS_INS_CARD_ID_MIN } from '~~/stubs/create-ltcs-ins-card-stub'

export function createLtcsInsCardResponseStub (id: LtcsInsCardId = LTCS_INS_CARD_ID_MIN): LtcsInsCardsApi.GetResponse {
  return {
    ltcsInsCard: createLtcsInsCardStub(id)
  }
}
