<template>
  <clienvy-field-label :label="$t('_field.connection_secret')">

    <div class="connection-secret-box">
      <span v-if="!secret" class="connection-secret-dots">••••••••••••</span>
      <code v-else class="connection-secret-value">{{ secret }}</code>

      <div class="connection-secret-actions">
        <button
            v-if="!secret && !revealed"
            type="button"
            class="connection-secret-action"
            @click="reveal"
        >
          <clienvy-icon name="eye" :size="14"/>
          <span>{{ $t('_button.reveal') }}</span>
        </button>

        <button
            v-if="secret"
            type="button"
            class="connection-secret-action"
            @click="copy"
        >
          <clienvy-icon name="copy" :size="14"/>
          <span>{{ $t('_button.copy') }}</span>
        </button>

        <button
            v-if="!secret"
            type="button"
            class="connection-secret-action connection-secret-action--danger"
            @click="resetOpen = true"
        >
          <clienvy-icon name="reset" :size="14"/>
          <span>{{ $t('_button.reset') }}</span>
        </button>
      </div>
    </div>
  </clienvy-field-label>

  <reset-secret-modal v-model="resetOpen" @success="onResetSuccess"/>
</template>

<script setup>
import {ref, computed} from 'vue';
import {ajax} from '../../../api.js';
import {t} from '../../../i18n.js';
import {popcorn} from '../../../popcorn.js';
import {state, onConnectionReset} from '../../../state.js';
import ClienvyIcon from '../../../components/ClienvyIcon.vue';
import ClienvyFieldLabel from '../../../components/ClienvyFieldLabel.vue';
import ResetSecretModal from '../modal/ResetSecretModal.vue';

const secret = ref('');
const resetOpen = ref(false);

const revealed = computed(() => state.secretRevealed);

async function reveal() {
  try {
    const res = await ajax('clienvy_reveal_secret');
    secret.value = res.secret;
    state.secretRevealed = true;
  } catch (e) {
    popcorn('error', e.message || t('_error.generic'));
  }
}

async function copy() {
  const value = secret.value;
  if (!value) return;
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(value);
    } else {
      fallbackCopy(value);
    }
  } catch {
    fallbackCopy(value);
  }
  popcorn('success', t('_admin._frontend._settings._connection_secret.connection_secret_copied'));
}

function fallbackCopy(text) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'absolute';
  ta.style.left = '-9999px';
  document.body.appendChild(ta);
  ta.select();
  document.execCommand('copy');
  ta.remove();
}

function onResetSuccess(newSecret) {
  secret.value = newSecret;
  onConnectionReset();
}
</script>

<style scoped lang="scss">
.connection-secret-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--clienvy-form-field-background-color);
  border: var(--clienvy-form-field-border-width) solid var(--clienvy-form-field-border-color);
  border-radius: var(--clienvy-form-field-border-radius);
  box-shadow: var(--clienvy-form-field-box-shadow);
  padding: 4px 6px 4px 18px;
  min-height: 46px;

  .connection-secret-dots {
    flex: 1;
    font-size: 13px;
    letter-spacing: 3px;
    color: var(--clienvy-form-field-color);
    line-height: 1;
    user-select: none;
  }

  .connection-secret-value {
    flex: 1;
    min-width: 0;
    font-family: "SF Mono", "Fira Code", "Fira Mono", "Courier New", monospace;
    font-size: 14px;
    color: var(--clienvy-form-field-color);
    word-break: break-all;
    line-height: 1.5;
    background: none;
    padding: 0;
    border: none;
  }

  .connection-secret-actions {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;

    .connection-secret-action {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      height: 32px;
      padding: 0 12px;
      background: transparent;
      border: 0;
      border-radius: 12px;
      font-family: var(--clienvy-text-font-family);
      font-size: 13px;
      font-weight: 400;
      color: var(--clienvy-bold-color);
      cursor: pointer;
      transition: background 0.2s, color 0.2s;

      &:hover {
        background: var(--clienvy-button-flat-background-color);
        color: var(--clienvy-button-flat-color);
      }

      &.connection-secret-action--danger:hover {
        background: var(--clienvy-tag-red-background-color);
        color: var(--clienvy-tag-red-color);
      }
    }
  }
}
</style>
