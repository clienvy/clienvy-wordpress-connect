<template>
  <header class="clienvy-page-header">
    <h1>{{ $t('_admin._frontend._settings._page.title') }}</h1>
    <span class="clienvy-version">v{{ version }}</span>
  </header>

  <connection/>
  <smtp-relay/>

  <clienvy-popcorn/>
</template>

<script setup>
import {state} from './state.js';
import Connection from './features/connection/Connection.vue';
import SmtpRelay from './features/smtp/SmtpRelay.vue';
import ClienvyPopcorn from './components/ClienvyPopcorn.vue';

const version = state.version;
</script>

<style scoped lang="scss">
.clienvy-page-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 24px 0 18px;

  h1 {
    font-size: 20px;
    font-weight: 700;
    color: var(--clienvy-heading-color);
  }
}

.clienvy-version {
  font-size: 11px;
  font-weight: 600;
  color: #fff;
  background: var(--clienvy-primary-color);
  padding: 2px 9px;
  border-radius: 20px;
  letter-spacing: 0.3px;
}
</style>
