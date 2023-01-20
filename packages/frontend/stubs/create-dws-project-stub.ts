/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DayOfWeek } from '@zinger/enums/lib/day-of-week'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { Recurrence } from '@zinger/enums/lib/recurrence'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { range } from '@zinger/helpers'
import { createDwsServiceOptions } from '~/composables/create-service-options'
import { filterDwsProjectServiceMenuByCategory } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { Contract } from '~/models/contract'
import { DwsProject, DwsProjectId } from '~/models/dws-project'
import { $datetime } from '~/services/datetime-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createDwsProjectServiceMenuStubs } from '~~/stubs/create-dws-project-service-menu-stub'
import { PROJECT_ID_MIN } from '~~/stubs/create-ltcs-project-stub'
import { createOwnExpenseProgramIndexResponseStub } from '~~/stubs/create-own-expense-program-index-response-stub'
import { STAFF_ID_MAX, STAFF_ID_MIN } from '~~/stubs/create-staff-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { STUB_DEFAULT_SEED } from '~~/stubs/index'

export function createDwsProjectStub (
  id: DwsProjectId = PROJECT_ID_MIN,
  contract: Contract = createContractStub(id)
): DwsProject {
  const seed = STUB_DEFAULT_SEED + `${id}`.padStart(8, '0')
  const faker = createFaker(seed)
  const ramen = ramenIpsum.factory(seed)
  const userId = contract.userId
  const menus = filterDwsProjectServiceMenuByCategory(createDwsProjectServiceMenuStubs())
  const ownExpense = createOwnExpenseProgramIndexResponseStub({ all: true })
  return {
    id,
    contractId: contract.id,
    officeId: contract.userId === 1 ? 8 : contract.officeId,
    userId,
    staffId: contract.userId === 1 ? 6 : faker.intBetween(STAFF_ID_MIN, STAFF_ID_MAX),
    writtenOn: faker.randomDateString(),
    effectivatedOn: faker.randomDateString(),
    requestFromUser: ramen.ipsum(200),
    requestFromFamily: ramen.ipsum(200),
    objective: ramen.ipsum(200),
    programs: range(1, faker.intBetween(1, 5)).map(summaryIndex => {
      const category = faker.randomElement(DwsProjectServiceCategory.values)
      const categoryMenus = menus(category).map(x => x.value)
      const contents = range(1, faker.intBetween(1, 10)).map(() => ({
        menuId: faker.randomElement(categoryMenus),
        content: ramen.ipsum(10),
        duration: faker.randomBoolean() ? faker.intBetween(10, 60) : undefined,
        memo: faker.randomBoolean() ? ramen.ipsum(10) : ''
      }))
      const contentsDurationSum = contents.reduce((content, val) => content + (val.duration ?? 0), 0)
      const programDate = $datetime.parse(faker.randomDateTimeString())
      const slot = {
        start: programDate.toFormat('HH:mm'),
        end: programDate.plus({ minutes: contentsDurationSum }).toFormat('HH:mm')
      }
      const options = createDwsServiceOptions('project', category).map(v => v.code)
      const ownExpenseProgramId = category === DwsProjectServiceCategory.ownExpense
        ? faker.randomElement(ownExpense.list.filter(x => x.officeId === contract.officeId))?.id
        : undefined
      return {
        summaryIndex,
        category,
        recurrence: faker.randomElement(Recurrence.values),
        dayOfWeeks: faker.randomArray(DayOfWeek.values, faker.intBetween(1, 7)),
        slot,
        headcount: faker.intBetween(1, 2) as 1 | 2,
        ownExpenseProgramId,
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

export function createDwsProjectStubs (xs: Contract[]): DwsProject[] {
  return xs
    .filter(contract => contract.serviceSegment === ServiceSegment.disabilitiesWelfare)
    .map(contract => createDwsProjectStub(contract.id, contract))
}
