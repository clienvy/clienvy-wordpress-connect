<template>
  <clienvy-field-label v-if="label" :label="label">
    <div class="clienvy-logs-container">
      <p v-if="!entries.length" class="clienvy-logs-empty">—</p>
      <div
          v-for="(entry, index) in entries"
          :key="index"
          class="clienvy-logs-entry"
      >
        {{ entry.message }}
      </div>
    </div>
  </clienvy-field-label>
</template>

<script setup>
import {computed} from 'vue';
import ClienvyFieldLabel from './ClienvyFieldLabel.vue';

const props = defineProps({
  label: {type: String, default: ''},
  logs: {type: Array, default: () => []},
});

const entries = computed(() =>
    props.logs
        .map((item) => {
          if (item == null) return null;
          if (typeof item === 'string') return item === '' ? null : {message: item};
          if (typeof item === 'object') return {message: item.message ?? String(item)};
          return {message: String(item)};
        })
        .filter(Boolean)
);
</script>

<style scoped lang="scss">

.clienvy-logs-container {
  background: var(--clienvy-logs-background-color);
  border: 1px solid var(--clienvy-logs-border-color);
  border-radius: var(--clienvy-logs-border-radius);
  box-shadow: var(--clienvy-logs-box-shadow);
  padding: 14px 16px;
  max-height: 260px;
  overflow-y: auto;
  font-family: var(--clienvy-logs-font-family);
  font-size: var(--clienvy-logs-font-size);
  line-height: 1.6;

  &::-webkit-scrollbar {
    width: 6px;
  }

  &::-webkit-scrollbar-track {
    background: transparent;
  }

  &::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;

    &:hover {
      background: rgba(0, 0, 0, 0.2);
    }
  }

  .clienvy-logs-entry {
    display: flex;
    gap: 12px;
    color: var(--clienvy-logs-color);
    word-break: break-word;
  }

  .clienvy-logs-empty {
    margin: 0;
    color: var(--clienvy-logs-color);
    font-family: var(--clienvy-text-font-family);
    font-size: 13px;
  }
}
</style>
