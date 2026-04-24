import { reactive } from 'vue';

const data = window.clienvyAdmin || {};

export const state = reactive({
	version: data.version || '',
	connected: !!data.connected,
	orgName: data.orgName || '',
	secretRevealed: !!data.secretRevealed,
	currentUserEmail: data.currentUserEmail || '',
	smtp: {
		enabled: !!data.smtp?.enabled,
		host: data.smtp?.host || '',
		port: data.smtp?.port || '',
		username: data.smtp?.username || '',
		senderName: data.smtp?.senderName || '',
		senderEmail: data.smtp?.senderEmail || '',
		replyToEnabled: !!data.smtp?.replyToEnabled,
		replyToName: data.smtp?.replyToName || '',
		replyToEmail: data.smtp?.replyToEmail || '',
	},
});

export function onConnectionReset() {
	state.connected = false;
	state.orgName = '';
	state.secretRevealed = true;
	state.smtp.enabled = false;
}
