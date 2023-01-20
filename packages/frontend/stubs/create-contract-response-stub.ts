/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContractId } from '~/models/contract'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { CONTRACT_ID_MIN, createContractStub } from '~~/stubs/create-contract-stub'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

export function createContractResponseStub (
  id: ContractId = CONTRACT_ID_MIN
): DwsContractsApi.GetResponse | LtcsContractsApi.GetResponse {
  const contract = createContractStub(id)
  return deleteUndefinedProperties({
    contract
  })
}
