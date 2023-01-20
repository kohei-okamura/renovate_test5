import { dwsContractStoreKey } from '~/composables/stores/use-dws-contract-store'
import { ltcsContractStoreKey } from '~/composables/stores/use-ltcs-contract-store'
import { userStoreKey } from '~/composables/stores/use-user-store'
import { useConfirmFunction } from '~/composables/use-confirm-function'
import { useInjected } from '~/composables/use-injected'
import { Contract } from '~/models/contract'
import { UserId } from '~/models/user'
import { RefOrValue } from '~/support/reactive'

type DisableContractFunctionParams = {
  contract: RefOrValue<Contract | undefined>
  type: 'dws' | 'ltcs'
  userId: UserId
}

export const useDisableContractFunction = () => {
  const userStore = useInjected(userStoreKey)

  const disableContractFunction = ({ userId, contract, type }: DisableContractFunctionParams) => {
    const contractStore = useInjected(type === 'dws' ? dwsContractStoreKey : ltcsContractStoreKey)
    return useConfirmFunction(contract, ({ id }) => {
      return {
        actionName: '実行',
        messageOnConfirm: '契約を無効にします。\n\n本当によろしいですか？',
        messageOnSuccess: '契約を無効にしました。',
        messageOnFailure: '契約の無効化に失敗しました。',
        returnTo: `/users/${userId}#${type}`,
        callback: async () => {
          await contractStore.disable({ id, userId })
          await userStore.get({ id: userId })
        }
      }
    })
  }

  return {
    disableContractFunction
  }
}
