<template>
	<clienvy-modal
		:model-value="modelValue"
		:title="$t('_admin._frontend._settings._connection_secret.confirm_reset_title')"
		:close-on-backdrop="!loading"
		@update:model-value="onUpdate"
	>
		<p>{{ $t('_admin._frontend._settings._connection_secret.confirm_reset') }}</p>

		<div class="reset-secret-actions">
			<clienvy-button
				variant="flat"
				:label="$t('_button.cancel')"
				:disabled="loading"
				@click="cancel"
			/>
			<clienvy-button
				variant="danger"
				:label="$t('_button.reset')"
				:spinning="loading"
				@click="reset"
			/>
		</div>
	</clienvy-modal>
</template>

<script setup>
import { ref } from 'vue';
import { ajax } from '../../../api.js';
import { t } from '../../../i18n.js';
import { popcorn } from '../../../popcorn.js';
import ClienvyModal from '../../../components/ClienvyModal.vue';
import ClienvyButton from '../../../components/ClienvyButton.vue';

defineProps({
	modelValue: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(['update:modelValue', 'success']);

const loading = ref(false);

function onUpdate(value) {
	if (loading.value) return;
	emit('update:modelValue', value);
}

function cancel() {
	emit('update:modelValue', false);
}

async function reset() {
	loading.value = true;
	try {
		const res = await ajax('clienvy_reset_secret');
		popcorn('success', t('_admin._frontend._settings._connection_secret.reset_success'));
		emit('success', res.secret);
		emit('update:modelValue', false);
	} catch (e) {
		popcorn('error', e.message || t('_error.generic'));
	} finally {
		loading.value = false;
	}
}
</script>

<style scoped lang="scss">
.reset-secret-actions {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
}
</style>
