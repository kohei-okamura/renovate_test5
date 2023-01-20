<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <div data-z-action-button :class="$style.root">
    <v-btn
      class="font-weight-bold white--text"
      color="primary"
      depressed
      type="submit"
      :icon="false"
      :text="false"
      v-bind="data.attrs"
    >
      <v-icon left>{{ props.icon }}</v-icon>
      <span>{{ props.text }}</span>
    </v-btn>
  </div>
</template>

<style lang="scss" module>
.root {
  margin: 8px auto 0;
  text-align: center;

  :global(.z-app--mobile) & {
    margin: 0 16px;

    :global(.v-btn) {
      width: 100%;
    }
  }
}
</style>
