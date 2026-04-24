<template>
  <clienvy-modal
      :model-value="modelValue"
      :title="$t('_admin._frontend._settings._smtp._test.modal_title')"
      :close-on-backdrop="!loading"
      @update:model-value="onUpdate"
  >
    <clienvy-form :submit="send">
      <p>{{ $t('_admin._frontend._settings._smtp._test.modal_intro') }}</p>

      <clienvy-input
          v-model="email"
          type="email"
          :label="$t('_field.recipient')"
          :disabled="loading"
      />

      <clienvy-logs
          v-if="logEntries.length"
          :label="$t('_word.technical_details')"
          :logs="logEntries"
      />

      <clienvy-button
          :full-size="true"
          :label="loading ? $t('_button.sending') : $t('_button.send_test_mail')"
          :spinning="loading"
          @click="send"
      />
    </clienvy-form>
  </clienvy-modal>
</template>

<script setup>
import {ref, watch} from 'vue';
import {ajax} from '../../../api.js';
import {t} from '../../../i18n.js';
import {popcorn} from '../../../popcorn.js';
import {state} from '../../../state.js';
import ClienvyModal from '../../../components/ClienvyModal.vue';
import ClienvyForm from '../../../components/ClienvyForm.vue';
import ClienvyInput from '../../../components/ClienvyInput.vue';
import ClienvyButton from '../../../components/ClienvyButton.vue';
import ClienvyLogs from '../../../components/ClienvyLogs.vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const email = ref(state.currentUserEmail);
const loading = ref(false);
const logEntries = ref([]);

watch(
    () => props.modelValue,
    (open) => {
      if (open) {
        logEntries.value = [];
        email.value = state.currentUserEmail;
      }
    }
);

function onUpdate(value) {
  if (loading.value) return;
  emit('update:modelValue', value);
}

async function send() {
  if (loading.value) return;

  if (!email.value.trim()) {
    popcorn('error', t('_admin._frontend._settings._smtp._test.empty_email'));
    return;
  }

  loading.value = true;
  logEntries.value = [];

  try {
    const res = await ajax('clienvy_send_test_email', {email: email.value});
    popcorn('success', res?.message || t('_admin._frontend._settings._smtp._test.success'));
    emit('update:modelValue', false);
  } catch (e) {
    popcorn('error', e.message || t('_admin._frontend._settings._smtp._test.generic_error'));
    logEntries.value = normalizeDetails(e.details);
  } finally {
    loading.value = false;
  }
}

function normalizeDetails(details) {
  if (!details) return [];
  if (Array.isArray(details)) return details;
  if (typeof details === 'string') {
    return details.split(/\r?\n/).filter((line) => line !== '').map((line) => ({message: line}));
  }
  return [];
}
</script>
