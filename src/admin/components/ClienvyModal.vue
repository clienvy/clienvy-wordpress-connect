<template>
  <transition name="clienvy-modal">
    <div v-if="modelValue" class="clienvy-modal-mask" @click="onMaskClick">
      <div class="clienvy-modal" role="dialog" aria-modal="true">
        <header class="clienvy-modal-header">
          <h3 v-if="title" class="clienvy-modal-title">{{ title }}</h3>
          <button
              type="button"
              class="clienvy-modal-close"
              :aria-label="$t('_button.close')"
              @click="close"
          >
            <clienvy-icon name="close" :size="16"/>
          </button>
        </header>

        <div class="clienvy-modal-body">
          <slot/>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import {onMounted, onBeforeUnmount} from 'vue';
import ClienvyIcon from './ClienvyIcon.vue';

const props = defineProps({
  modelValue: {type: Boolean, default: false},
  title: {type: String, default: ''},
  closeOnBackdrop: {type: Boolean, default: true},
});

const emit = defineEmits(['update:modelValue', 'close']);

function close() {
  emit('update:modelValue', false);
  emit('close');
}

function onMaskClick(event) {
  if (event.target === event.currentTarget && props.closeOnBackdrop) {
    close();
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && props.modelValue) {
    close();
  }
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<style scoped lang="scss">
.clienvy-modal-mask {
  position: fixed;
  inset: 0;
  z-index: 100001;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: var(--clienvy-modal-mask-background-color);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  font-family: var(--clienvy-text-font-family);
}

.clienvy-modal {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 500px;
  max-height: 85vh;
  overflow-y: auto;
  background: var(--clienvy-modal-background-color);
  border-radius: var(--clienvy-modal-border-radius);
  border: 1px solid var(--clienvy-light-border-color);
  box-shadow: var(--clienvy-modal-box-shadow);

  .clienvy-modal-header {
    display: flex;
    align-items: flex-end;
    gap: 15px;
    padding: 36px 42px 20px 42px;
  }

  .clienvy-modal-title {
    flex: 1;
    min-width: 0;
    font-size: 21px;
    font-weight: 400;
    color: var(--clienvy-heading-color);
    margin: 0;
    line-height: 1.3;
  }

  .clienvy-modal-close {
    flex-shrink: 0;
    width: 31px;
    height: 31px;
    border: 0;
    background: var(--clienvy-button-flat-background-color);
    color: var(--clienvy-button-flat-icon-color);
    cursor: pointer;
    border-radius: 100px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: -4px;
    padding: 0;
    transition: background 0.2s, color 0.2s;

    &:hover {
      background: var(--clienvy-button-flat-hover-background-color);
      color: var(--clienvy-button-flat-hover-icon-color);
    }
  }

  .clienvy-modal-body {
    padding: 0 42px 36px 42px;
    font-family: var(--clienvy-text-font-family);
    font-size: var(--clienvy-text-font-size);
    font-weight: var(--clienvy-text-font-weight);
    line-height: 1.45;

    :deep(p) {
      margin-bottom: 10px;
    }

    :deep(p + p) {
      margin-top: 10px;
    }

    :deep(b) {
      color: var(--clienvy-bold-color);
      font-weight: 500;
    }
  }
}

.clienvy-modal-enter-active,
.clienvy-modal-leave-active {
  transition: opacity 0.2s ease;
}

.clienvy-modal-enter-from,
.clienvy-modal-leave-to {
  opacity: 0;
}

.clienvy-modal-enter-active .clienvy-modal,
.clienvy-modal-leave-active .clienvy-modal {
  transition: transform 0.2s ease;
}

.clienvy-modal-enter-from .clienvy-modal,
.clienvy-modal-leave-to .clienvy-modal {
  transform: scale(0.97);
}
</style>
