<template>
  <teleport to="body">
    <div class="clienvy-admin clienvy-admin--ready clienvy-popcorn-items">
      <transition-group name="clienvy-popcorn" tag="div" class="clienvy-popcorn-stack">
        <div
            v-for="item in popcornState.items"
            :key="item.id"
            class="clienvy-popcorn"
            :class="`clienvy-popcorn--${item.type}`"
            role="status"
            @click="dismissPopcorn(item.id)"
        >
					<span class="clienvy-popcorn-icon">
						<clienvy-icon :name="iconFor(item.type)" :size="14"/>
					</span>
          <div class="clienvy-popcorn-text">{{ item.text }}</div>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import {popcornState, dismissPopcorn} from '../popcorn.js';
import ClienvyIcon from './ClienvyIcon.vue';

function iconFor(type) {
  if (type === 'error') return 'close';
  if (type === 'success') return 'check';
  return 'check';
}
</script>

<style scoped lang="scss">
.clienvy-popcorn-items {
  position: fixed;
  bottom: 35px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 100002;
  width: 600px;
  max-width: calc(100% - 40px);
  margin: 0;
  padding: 0;
  opacity: 1;
  pointer-events: none;
  font-family: var(--clienvy-text-font-family);

  .clienvy-popcorn-stack {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .clienvy-popcorn {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--clienvy-popcorn-background-color);
    border-radius: 10px;
    box-shadow: inset 3px 0 0 var(--clienvy-popcorn-accent-color),
    inset 0 0 0 1px var(--clienvy-popcorn-border-color),
    var(--clienvy-popcorn-shadow);
    color: var(--clienvy-popcorn-text-color);
    font-family: var(--clienvy-text-font-family);
    font-size: 15px;
    font-weight: 300;
    overflow: hidden;
    pointer-events: auto;
    cursor: pointer;

    .clienvy-popcorn-icon {
      flex-shrink: 0;
      width: 18px;
      height: 18px;
      margin-left: 24px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--clienvy-popcorn-icon-color);
    }

    .clienvy-popcorn-text {
      flex: 1;
      padding: 15px 40px 15px 20px;
      line-height: 1.4;
      font-size: 15px;
      letter-spacing: 0.3px;
    }

    &.clienvy-popcorn--success {
      --clienvy-popcorn-accent-color: var(--clienvy-popcorn-success-color);

      .clienvy-popcorn-icon {
        background: var(--clienvy-popcorn-success-color);
      }
    }

    &.clienvy-popcorn--error {
      --clienvy-popcorn-accent-color: var(--clienvy-popcorn-error-color);

      .clienvy-popcorn-icon {
        background: var(--clienvy-popcorn-error-color);
      }
    }

    &.clienvy-popcorn--info {
      --clienvy-popcorn-accent-color: var(--clienvy-primary-color);

      .clienvy-popcorn-icon {
        background: var(--clienvy-primary-color);
      }
    }
  }

  .clienvy-popcorn-enter-active {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
  }

  .clienvy-popcorn-leave-active {
    transition: transform 0.2s ease-in, opacity 0.2s ease-in;
  }

  .clienvy-popcorn-enter-from {
    opacity: 0;
    transform: scale(0.94) translateY(6px);
  }

  .clienvy-popcorn-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(6px);
  }

  .clienvy-popcorn-move {
    transition: transform 0.25s ease;
  }
}
</style>
