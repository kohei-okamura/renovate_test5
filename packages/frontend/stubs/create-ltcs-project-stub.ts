/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DayOfWeek } from '@zinger/enums/lib/day-of-week'
import { LtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { Recurrence } from '@zinger/enums/lib/recurrence'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { range } from '@zinger/helpers'
import { createLtcsServiceOptions } from '~/composables/create-service-options'
import {
  filterLtcsProjectServiceMenuByCategory
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { Contract } from '~/models/contract'
import { LtcsProject, LtcsProjectId } from '~/models/ltcs-project'
import { $datetime } from '~/services/datetime-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import {
  getLtcsHomeVisitLongTermCareServiceCodeList
} from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-stub'
import { createLtcsProjectServiceMenuStubs } from '~~/stubs/create-ltcs-project-service-menu-stub'
import { createOwnExpenseProgramIndexResponseStub } from '~~/stubs/create-own-expense-program-index-response-stub'
import { STAFF_ID_MAX, STAFF_ID_MIN } from '~~/stubs/create-staff-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { ID_MIN, STUB_DEFAULT_SEED } from '~~/stubs/index'

export const PROJECT_ID_MIN = ID_MIN

export function createLtcsProjectStub (
  id: LtcsProjectId = PROJECT_ID_MIN,
  contract: Contract = createContractStub(id)
): LtcsProject {
  const seed = STUB_DEFAULT_SEED + `${id}`.padStart(8, '0')
  const faker = createFaker(seed)
  const serviceCodes = getLtcsHomeVisitLongTermCareServiceCodeList()
  const ramen = ramenIpsum.factory(seed)
  const longTermTimes = [faker.randomDateTimeString(), faker.randomDateTimeString()]
  const shortTermTimes = [faker.randomDateTimeString(), faker.randomDateTimeString()]
  const menus = filterLtcsProjectServiceMenuByCategory(createLtcsProjectServiceMenuStubs())
  const ownExpense = createOwnExpenseProgramIndexResponseStub({ all: true })
  return {
    id,
    contractId: contract.id,
    officeId: contract.officeId,
    userId: contract.userId,
    staffId: faker.intBetween(STAFF_ID_MIN, STAFF_ID_MAX),
    writtenOn: faker.randomDateString(),
    effectivatedOn: faker.randomDateString(),
    problem: ramen.ipsum(100),
    requestFromUser: ramen.ipsum(100),
    requestFromFamily: ramen.ipsum(100),
    longTermObjective: {
      term: {
        start: longTermTimes[0] < longTermTimes[1] ? longTermTimes[0] : longTermTimes[1],
        end: longTermTimes[0] > longTermTimes[1] ? longTermTimes[0] : longTermTimes[1]
      },
      text: ramen.ipsum(100)
    },
    shortTermObjective: {
      term: {
        start: shortTermTimes[0] < shortTermTimes[1] ? shortTermTimes[0] : shortTermTimes[1],
        end: shortTermTimes[0] > shortTermTimes[1] ? shortTermTimes[0] : shortTermTimes[1]
      },
      text: ramen.ipsum(100)
    },
    programs: range(1, faker.intBetween(1, 5)).map(programIndex => {
      const category = faker.randomElement(LtcsProjectServiceCategory.values)
      const isPhysicalCareAndHousework = category === LtcsProjectServiceCategory.physicalCareAndHousework
      const categoryMenus = menus(category).map(x => x.value)
      const amountsArray = isPhysicalCareAndHousework
        ? [LtcsProjectAmountCategory.physicalCare, LtcsProjectAmountCategory.housework]
        : [category as LtcsProjectAmountCategory]
      const contents = range(1, faker.intBetween(1, 10))
        .map(() => ({
          menuId: faker.randomElement(categoryMenus),
          duration: faker.randomBoolean() ? faker.intBetween(10, 60) : undefined,
          content: faker.randomBoolean() ? ramen.ipsum(10) : '',
          memo: faker.randomBoolean() ? ramen.ipsum(10) : ''
        }))
      const contentsDurationSum = contents.reduce((content, val) => content + (val.duration ?? 0), 0)
      const programDate = $datetime.parse(faker.randomDateTimeString())
      const slot = {
        start: programDate.toFormat('HH:mm'),
        end: programDate.plus({ minutes: contentsDurationSum }).toFormat('HH:mm')
      }
      const amountFakeInt = faker.intBetween(1, contentsDurationSum - 1)
      const amounts = amountsArray.map((x, i) => ({
        category: x,
        amount: amountsArray.length === 1
          ? contentsDurationSum
          : i === 0 ? amountFakeInt : contentsDurationSum - amountFakeInt
      }))
      const options = createLtcsServiceOptions('project', category).map(v => v.code)
      const ownExpenseProgramId = category === LtcsProjectServiceCategory.ownExpense
        ? faker.randomElement(ownExpense.list.filter(x => x.officeId === contract.officeId))?.id
        : undefined
      return {
        programIndex,
        category,
        recurrence: faker.randomElement(Recurrence.values),
        timeframe: faker.randomElement(Timeframe.values),
        dayOfWeeks: faker.randomArray(DayOfWeek.values, faker.intBetween(1, 7)),
        slot,
        amounts,
        headcount: faker.intBetween(1, 2),
        ownExpenseProgramId,
        serviceCode: category !== LtcsProjectServiceCategory.ownExpense
          ? faker.randomElement(serviceCodes)
          : '',
        options: faker.randomArray(options, faker.intBetween(1, options.length)),
        contents,
        note: faker.randomBoolean() ? ramen.ipsum(10) : ''
      }
    }),
    isEnabled: true,
    version: id,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createLtcsProjectStubs (xs: Contract[]): LtcsProject[] {
  return xs
    .filter(contract => contract.serviceSegment === ServiceSegment.longTermCare)
    .map(contract => createLtcsProjectStub(contract.id, contract))
}
