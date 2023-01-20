import { Range } from 'immutable'
import { Shift } from '~/models/shift'
import { $datetime } from '~/services/datetime-service'
import { CreateStubs } from '~~/stubs'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createShiftStubsForContract } from '~~/stubs/create-shift-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'

export const createCallingsStub: CreateStubs<Shift> = () => {
  const now = $datetime.now.startOf('day')
  return Range(USER_ID_MIN, USER_ID_MAX)
    .flatMap(createContractStubsForUser)
    .flatMap(x => createShiftStubsForContract(x, now, 0))
    .take(10)
    .sortBy(x => $datetime.parse(x.schedule.start).toMillis())
    .toArray()
}
