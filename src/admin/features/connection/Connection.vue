<template>
  <clienvy-card
      :title="title"
      :tag="tag"
      :info="info"
      collapsible
      :default-open="!state.connected"
  >
    <connection-secret/>
  </clienvy-card>
</template>

<script setup>
import {computed} from 'vue';
import {t} from '../../i18n.js';
import {state} from '../../state.js';
import ClienvyCard from '../../components/ClienvyCard.vue';
import ConnectionSecret from './parts/ConnectionSecret.vue';

const title = computed(() =>
    state.connected
        ? t('_admin._frontend._settings._connection_secret._connected.title')
        : t('_admin._frontend._settings._connection_secret._disconnected.title')
);

const info = computed(() =>
    state.connected
        ? t('_admin._frontend._settings._connection_secret._connected.info')
        : t('_admin._frontend._settings._connection_secret._disconnected.info')
);

const tag = computed(() => ({
  label: state.connected ? (state.orgName || t('_word.connected')) : t('_word.disconnected'),
  color: state.connected ? 'green' : 'red',
}));
</script>
