<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-shift-list :class="{ [$style.mobile]: $vuetify.breakpoint.smAndDown }">
    <z-flex v-for="(x, i) in value" :key="i" class="align-baseline" :class="$style.item">
      <div :class="[$style.element, $style.schedule]">
        <z-time :value="x.schedule.start" />
        <span>-</span>
        <z-time :value="x.schedule.end" />
      </div>
      <div :class="[$style.element, $style.task]">
        <z-task-marker :task="x.task" />
      </div>
      <div :class="[$style.element, $style.user]">
        <span>{{ resolveUserName(x.userId) }}</span>
      </div>
      <div :class="[$style.element, $style.note]">{{ x.note || '-' }}</div>
    </z-flex>
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useUsers } from '~/composables/use-users'
import { Shift } from '~/models/shift'

type Props = {
  value: Shift[]
}

export default defineComponent<Props>({
  name: 'ZShiftList',
  props: {
    value: { type: [Array, Object], required: true }
  },
  setup () {
    return {
      ...useUsers({ permission: Permission.listShifts })
    }
  }
})
</script>

<style lang="scss" module>
.item {
  & + & {
    margin-top: 16px;
  }
}

.element {
  & + & {
    margin-left: 8px;
  }
}

.schedule {
  min-width: 6em;
  width: auto;
}

.task {
  min-width: 144px;
  width: auto;
}

.user {
  width: 8em;
}

.note {
  flex-basis: 0;
  flex-grow: 1;
}

.mobile {
  .item {
    flex-wrap: wrap;
  }

  .element {
    margin-left: 0;
  }

  .schedule {
    margin-left: 4px;
    order: 1;
  }

  .task {
    order: 0;
  }

  .user {
    margin: 8px 0 0 0;
    order: 2;
    width: 100%;
  }

  .note {
    margin: 4px 0 0 0;
    order: 3;
  }
}
</style>
