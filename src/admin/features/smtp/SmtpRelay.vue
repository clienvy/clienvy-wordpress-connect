<template>
  <clienvy-card
      :title="title"
      :tag="tag"
      :info="info"
      :collapsible="state.smtp.enabled"
      :default-open="!state.smtp.enabled"
  >
    <template v-if="state.smtp.enabled">
      <clienvy-form style="margin-bottom: 15px">
        <clienvy-display-text
            :model-value="server"
            :label="$t('_field.smtp_server')"
        />
        <clienvy-display-text
            :model-value="state.smtp.username"
            :label="$t('_field.smtp_user')"
        />
        <clienvy-display-text
            :model-value="sender"
            :label="$t('_field.smtp_sender')"
        />
        <clienvy-display-text
            v-if="state.smtp.replyToEnabled"
            :model-value="replyTo"
            :label="$t('_field.smtp_reply_to')"
        />
      </clienvy-form>

      <clienvy-button
          :label="$t('_button.test_smtp')"
          @click="testModalOpen = true"
      />

      <smtp-relay-test-modal v-model="testModalOpen"/>
    </template>
  </clienvy-card>
</template>

<script setup>
import {computed, ref} from 'vue';
import {t} from '../../i18n.js';
import {state} from '../../state.js';
import ClienvyCard from '../../components/ClienvyCard.vue';
import ClienvyForm from '../../components/ClienvyForm.vue';
import ClienvyDisplayText from '../../components/ClienvyDisplayText.vue';
import ClienvyButton from '../../components/ClienvyButton.vue';
import SmtpRelayTestModal from './modal/SmtpRelayTestModal.vue';

const testModalOpen = ref(false);

const tag = computed(() => ({
  label: state.smtp.enabled ? t('_word.active') : t('_word.inactive'),
  color: state.smtp.enabled ? 'green' : 'gray',
}));

const server = computed(() => {
  if (!state.smtp.host || !state.smtp.port) return '';
  return `${state.smtp.host}:${state.smtp.port}`;
});

const sender = computed(() => formatAddress(state.smtp.senderName, state.smtp.senderEmail));
const replyTo = computed(() => {
  if (!state.smtp.replyToEnabled) return '';
  return formatAddress(state.smtp.replyToName, state.smtp.replyToEmail);
});

const title = computed(() =>
    state.smtp.enabled
        ? t('_admin._frontend._settings._smtp._enabled.title')
        : t('_admin._frontend._settings._smtp._disabled.title')
);

const info = computed(() =>
    state.smtp.enabled
        ? t('_admin._frontend._settings._smtp._enabled.info')
        : t('_admin._frontend._settings._smtp._disabled.info')
);

function formatAddress(name, email) {
  if (name == '') return email;
  else return name + ' <' + email + '>';
}
</script>
