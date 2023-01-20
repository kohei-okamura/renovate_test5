<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { resolveSex } from '@zinger/enums/lib/sex'
import { VNode, VNodeData } from 'vue'
import { defineFunctionalComponent } from '~/components/tools/define-functional-component'
import ZDataCardItem from '~/components/ui/z-data-card-item.vue'
import ZDataCard from '~/components/ui/z-data-card.vue'
import { eraDate } from '~/composables/era-date'
import { User } from '~/models/user'
import { $icons } from '~/plugins/icons'

const generateCardData = (data: VNodeData): VNodeData => ({
  ...data,
  props: {
    title: '利用者情報'
  }
})

const generateItemData = (label: string, icon: string, value: string): VNodeData => ({
  props: {
    label,
    icon,
    value
  }
})

export default defineFunctionalComponent({
  name: 'ZUserSummary',
  functional: true,
  props: {
    user: { type: Object, required: true }
  },
  render (h, { data, props, slots }): VNode {
    const user: User = props.user
    return h(ZDataCard, generateCardData(data), [
      h(ZDataCardItem, generateItemData('利用者名', $icons.user, user.name.displayName)),
      h(ZDataCardItem, generateItemData('性別', $icons.sex, resolveSex(user.sex))),
      h(ZDataCardItem, generateItemData('生年月日', $icons.birthday, eraDate(user.birthday))),
      ...(slots().default || [])
    ])
  }
})
</script>
