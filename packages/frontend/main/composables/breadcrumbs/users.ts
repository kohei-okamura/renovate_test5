/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { Contract } from '~/models/contract'
import { DwsCertification } from '~/models/dws-certification'
import { DwsProject } from '~/models/dws-project'
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { LtcsProject } from '~/models/ltcs-project'
import { User } from '~/models/user'
import { UserDwsSubsidy } from '~/models/user-dws-subsidy'
import { UserLtcsSubsidy } from '~/models/user-ltcs-subsidy'
import { VBreadcrumb } from '~/models/vuetify'

const index = breadcrumb('利用者', '/users?restore=1')

const toUser = (user: User | undefined, fragment: string = ''): VBreadcrumb[] => [
  index,
  breadcrumb(user?.name.displayName ?? '', user ? `/users/${user.id}${fragment}` : '')
]

export const users = {
  index: [
    breadcrumb('利用者')
  ],
  new: [
    index,
    breadcrumb('利用者を登録')
  ],
  view: (user: User | undefined) => [
    index,
    breadcrumb(user?.name.displayName ?? '')
  ],
  edit: (user: User | undefined) => [
    ...toUser(user),
    breadcrumb('基本情報を編集')
  ],
  bankAccount: {
    edit: (user: User | undefined) => [
      ...toUser(user),
      breadcrumb('銀行口座情報を編集')
    ]
  },
  dwsCertifications: {
    view: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('受給者証詳細')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('受給者証を登録')
    ],
    edit: (user: User | undefined, certification: DwsCertification | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb(
        '受給者証詳細',
        user && certification ? `/users/${user.id}/dws-certifications/${certification.id}` : ''
      ),
      breadcrumb('編集')
    ]
  },
  dwsContracts: {
    view: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('契約詳細')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('契約情報を登録')
    ],
    edit: (user: User | undefined, contract: Contract | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('契約詳細', user && contract ? `/users/${user.id}/dws-contracts/${contract.id}` : ''),
      breadcrumb('編集')
    ]
  },
  dwsProjects: {
    view: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('計画詳細')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('計画情報を登録')
    ],
    edit: (user: User | undefined, project: DwsProject | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('計画詳細', user && project ? `/users/${user.id}/dws-projects/${project.id}` : ''),
      breadcrumb('編集')
    ]
  },
  dwsSubsidies: {
    view: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('自治体助成情報')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('自治体助成情報を登録')
    ],
    edit: (user: User | undefined, subsidy: UserDwsSubsidy | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('自治体助成情報', user && subsidy ? `/users/${user.id}/dws-subsidies/${subsidy.id}` : ''),
      breadcrumb('編集')
    ]
  },
  dwsCalcSpecs: {
    new: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('利用者別算定情報を登録')
    ],
    edit: (user: User | undefined) => [
      ...toUser(user, '#dws'),
      breadcrumb('利用者別算定情報'),
      breadcrumb('編集')
    ]
  },
  ltcsContracts: {
    view: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('契約詳細')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('契約情報を登録')
    ],
    edit: (user: User | undefined, contract: Contract | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb(
        '契約詳細',
        user && contract ? `/users/${user.id}/ltcs-contracts/${contract.id}` : ''
      ),
      breadcrumb('編集')
    ]
  },
  ltcsInsCards: {
    view: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('被保険者証詳細')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('被保険者証を登録')
    ],
    edit: (user: User | undefined, ltcsInsCard: LtcsInsCard | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('被保険者証詳細', user && ltcsInsCard ? `/users/${user.id}/ltcs-ins-cards/${ltcsInsCard.id}` : ''),
      breadcrumb('編集')
    ]
  },
  ltcsProjects: {
    view: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('計画詳細')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('計画情報を登録')
    ],
    edit: (user: User | undefined, project: LtcsProject | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('計画詳細', user && project ? `/users/${user.id}/ltcs-projects/${project.id}` : ''),
      breadcrumb('編集')
    ]
  },
  ltcsSubsidies: {
    view: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('公費情報')
    ],
    new: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('公費情報を登録')
    ],
    edit: (user: User | undefined, subsidy: UserLtcsSubsidy | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('公費情報', user && subsidy ? `/users/${user.id}/ltcs-subsidies/${subsidy.id}` : ''),
      breadcrumb('編集')
    ]
  },
  ltcsCalcSpecs: {
    new: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('利用者別算定情報を登録')
    ],
    edit: (user: User | undefined) => [
      ...toUser(user, '#ltcs'),
      breadcrumb('利用者別算定情報'),
      breadcrumb('編集')
    ]
  }
} as const
