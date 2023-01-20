<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div ref="container" data-z-overflow-shadow :class="overflowShadowClasses">
    <div :class="$style.scrollContainer">
      <span v-intersect:option="interSectionLeftOption"></span>
      <div class="flex-fill">
        <slot></slot>
      </div>
      <span v-intersect:option="interSectionRightOption"></span>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { reactive } from '@vue/composition-api'
import { templateRef } from '~/support/reactive'

export default defineComponent({
  name: 'ZOverflowShadow',
  setup () {
    const shadow = reactive({
      right: false,
      left: false
    })
    const container = templateRef<HTMLDivElement>()

    const options = {
      root: container.value,
      threshold: 0.3
    }
    const interSectionRightOption = {
      handler: ([{ isIntersecting }]: IntersectionObserverEntry[]) => {
        shadow.right = !isIntersecting
      },
      ...options
    }
    const interSectionLeftOption = {
      handler: ([{ isIntersecting }]: IntersectionObserverEntry[]) => {
        shadow.left = !isIntersecting
      },
      ...options
    }
    const { root, left, right } = useCssModule()
    return {
      container,
      interSectionLeftOption,
      interSectionRightOption,
      overflowShadowClasses: computed(
        () => [root, { [left]: shadow.left, [right]: shadow.right }]
      )
    }
  }
})
</script>

<style lang="scss" module>
@mixin shadow($offset-x) {
  content: '';
  height: 100%;
  opacity: 0;
  box-shadow: inset $offset-x 0 9px -7px rgba(0, 0, 0, 0.4);
  position: absolute;
  top: 0;
  width: 7px;
  z-index: 3;
}

.root {
  overflow: hidden;
  position: relative;

  &:before {
    @include shadow(7px);
    left: 0;
  }

  &:after {
    @include shadow(-7px);
    right: 0;
  }

  &.left:before,
  &.right:after {
    opacity: 1;
  }

  :global {
    .v-data-table {
      max-width: initial;
    }

    .v-data-table__wrapper {
      overflow: unset;
    }
  }
}

.scrollContainer {
  overflow: auto;
  display: flex;

  > span:last-child { // Firefoxでintersection observerが反応しないときがあるときため適用する
    min-width: 0.5px;
  }
}
</style>
