import { t } from './i18n.js';

export async function ajax(action, payload = {}) {
	const config = window.clienvyAdmin || {};
	const body = new URLSearchParams({ action, nonce: config.nonce || '' });

	for (const [key, value] of Object.entries(payload)) {
		body.append(key, value == null ? '' : String(value));
	}

	const response = await fetch(config.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		body,
	});

	if (!response.ok) {
		throw new Error('HTTP ' + response.status);
	}

	const json = await response.json();
	if (!json.success) {
		const data = json.data;
		const message = typeof data === 'string' ? data : (data?.message || t('_error.request_failed'));
		const error = new Error(message);
		if (data && typeof data === 'object' && data.details != null && data.details !== '') {
			error.details = data.details;
		}
		throw error;
	}
	return json.data;
}
