<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <v-menu offset-y transition="slide-y-transition">
    <template #activator="{ on }">
      <v-btn icon small text v-on="on">
        <v-icon>{{ parent.$icons.more }}</v-icon>
      </v-btn>
    </template>
    <v-list dense>
      <slot name="default"></slot>
    </v-list>
  </v-menu>
</template>
