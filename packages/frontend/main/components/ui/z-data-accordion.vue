<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-data-accordion :class="$style.root">
    <z-subheader v-if="title">{{ title }}</z-subheader>
    <v-expansion-panels v-model="syncedPanel" multiple>
      <v-expansion-panel v-for="(item, i) in items" :key="i" :class="$style.panel" data-expansion-panel>
        <v-expansion-panel-header :class="$style.header" data-expansion-panel-header>
          <div :class="$style.item">
            <div class="text-caption text--secondary" :class="[$style.label, firstHeader.class]">
              {{ firstHeader.text }}
            </div>
            <div class="text-body-2 text--primary" :class="$style.label">
              <slot :item="item" :name="'item.' + firstHeader.value"></slot>
            </div>
          </div>
        </v-expansion-panel-header>
        <v-expansion-panel-content :class="$style.content">
          <div v-for="(header, j) in options.headers.slice(1)" :key="j" :class="$style.item">
            <div class="text-caption text--secondary" :class="[header.class, $style.label]">
              {{ header.text }}
            </div>
            <div class="text-body-2 text--primary" :class="$style.value">
              <slot :item="item" :name="'item.' + header.value"></slot>
            </div>
          </div>
          <div v-if="isItemLinkEnabled" class="text-right" :class="$style.link">
            <v-btn color="primary" data-accordion-panel-link nuxt text :to="options.itemLink(item)">
              <span>{{ itemLinkText }}</span>
              <v-icon right>{{ $icons.forward }}</v-icon>
            </v-btn>
          </div>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <v-card v-if="!items.length">
      <v-card-text>
        {{ noDataText }}
      </v-card-text>
    </v-card>
    <div v-if="hasFooter" class="text-center" :class="$style.footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { nonEmpty } from '@zinger/helpers'
import { useAuth } from '~/composables/use-auth'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { ZDataAccordionOptions } from '~/models/z-data-accordion-options'

type Item = any

type Props = Readonly<{
  items: Item[]
  options: ZDataAccordionOptions<Item>
  panel: number[]
}>

export default defineComponent<Props>({
  name: 'ZDataAccordion',
  props: {
    items: { type: Array, required: true },
    options: { type: Object, required: true },
    panel: { type: Array, default: () => [] }
  },
  setup (props, context) {
    const { slots } = context
    const { isAuthorized } = useAuth()
    const hasFooter = computed(() => nonEmpty(slots.footer))
    const hasItemLink = computed(() => nonEmpty(props.options.itemLink))
    const hasItemLinkPermissions = computed(() => isAuthorized.value(props.options.itemLinkPermissions))
    const isItemLinkEnabled = computed(() => hasItemLink.value && hasItemLinkPermissions.value)
    const itemLinkText = computed(() => props.options.itemLinkText ?? '詳細を見る')
    const firstHeader = computed(() => props.options.headers[0])
    const noDataText = `該当する${props.options.content}は登録されていません。`
    const syncedPanel = useSyncedProp('panel', props, context)
    const title = computed(() => props.options.title ?? '')
    return {
      firstHeader,
      hasFooter,
      isItemLinkEnabled,
      itemLinkText,
      noDataText,
      syncedPanel,
      title
    }
  }
})
</script>

<style lang="scss" module>
.root {
  margin-top: 16px;

  .panel {
    padding: 8px 0;

    .header {
      padding: 0 16px 0 0;
    }

    .item {
      padding: 12px 16px;

      .value {
        margin-top: 4px;
      }
    }

    .link {
      padding: 0 16px;
    }
  }

  .footer {
    padding-top: 16px;
  }

  :global {
    .v-expansion-panel-content__wrap {
      padding: 0;
    }
  }
}
</style>
