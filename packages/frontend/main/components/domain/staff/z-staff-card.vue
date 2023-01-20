<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card title="基本情報">
    <z-data-card-item label="社員番号" :icon="$icons.employeeNumber" :value="employeeNumber" />
    <z-data-card-item label="状態" :icon="statusIcon" :value="resolveStaffStatus(status)" />
    <z-data-card-item label="氏名" :icon="$icons.staff" :value="name.displayName" />
    <z-data-card-item label="氏名：フリガナ" :value="name.phoneticDisplayName" />
    <z-data-card-item label="性別" :icon="$icons.sex" :value="resolveSex(sex)" />
    <z-data-card-item label="生年月日" :icon="$icons.birthday">
      <z-era-date :value="birthday" />
    </z-data-card-item>
    <z-data-card-item label="住所" :icon="$icons.addr">
      〒{{ addr.postcode }}<br>
      {{ resolvePrefecture(addr.prefecture) }}{{ addr.city }}{{ addr.street }}
      <template v-if="addr.apartment"><br>{{ addr.apartment }}</template>
    </z-data-card-item>
    <z-data-card-item label="電話番号" :icon="$icons.tel" :value="tel" />
    <z-data-card-item label="FAX番号" :value="fax || '-'" />
    <z-data-card-item label="メールアドレス" :icon="$icons.email">
      <a :href="`mailto:${email}`">{{ email }}</a>
    </z-data-card-item>
    <z-data-card-item label="資格" :icon="$icons.certification">
      <div v-if="certifications.length === 0">-</div>
      <template v-else>
        <v-chip v-for="x in certifications" :key="x" label small>{{ resolveCertification(x) }}</v-chip>
      </template>
    </z-data-card-item>
    <z-data-card-item label="所属事業所" :icon="$icons.office">
      <div v-if="offices.length === 0">-</div>
      <template v-else>
        <v-chip v-for="x in offices" :key="x.id" label small>{{ x.abbr }}</v-chip>
      </template>
    </z-data-card-item>
    <z-data-card-item label="所属事業所グループ" :icon="$icons.blank">
      <div v-if="offices.length === 0">-</div>
      <template v-else>
        <template v-for="(x, i) in offices">
          <v-chip v-if="x.officeGroupId !== undefined" :key="i" label small>
            {{ resolveOfficeGroupName(x.officeGroupId) }}
          </v-chip>
        </template>
      </template>
    </z-data-card-item>
    <z-data-card-item v-if="!hideRoles" label="ロール" :icon="$icons.role">
      <div v-if="roles.length === 0">-</div>
      <template v-else>
        <v-chip v-for="x in roles" :key="x.id" label small>{{ x.name }}</v-chip>
      </template>
    </z-data-card-item>
  </z-data-card>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolveCertification } from '@zinger/enums/lib/certification'
import { Permission } from '@zinger/enums/lib/permission'
import { resolvePrefecture } from '@zinger/enums/lib/prefecture'
import { resolveSex } from '@zinger/enums/lib/sex'
import { resolveStaffStatus } from '@zinger/enums/lib/staff-status'
import { pick } from '@zinger/helpers'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { useStaffStatusIcon } from '~/composables/use-staff-status-icon'
import { Staff } from '~/models/staff'

export default defineComponent<Staff>({
  name: 'ZStaffCard',
  props: {
    addr: { type: Object, required: true },
    birthday: { type: String, required: true },
    certifications: { type: Array, required: true },
    email: { type: String, required: true },
    employeeNumber: { type: [Number, String], required: true },
    fax: { type: String, required: true },
    hideRoles: { type: Boolean, default: false },
    name: { type: Object, required: true },
    offices: { type: Array, required: true },
    roles: { type: Array, default: () => [] },
    sex: { type: Number, required: true },
    tel: { type: String, required: true },
    status: { type: Number, required: true }
  },
  setup: props => {
    return {
      ...useOfficeGroups({ permission: Permission.updateStaffs }),
      ...useStaffStatusIcon(computed(() => pick(props, ['status']))),
      resolveCertification,
      resolvePrefecture,
      resolveSex,
      resolveStaffStatus
    }
  }
})
</script>
