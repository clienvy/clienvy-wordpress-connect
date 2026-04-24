import { createApp } from 'vue';
import App from './App.vue';
import i18n from './i18n.js';
import './styles/theme.css';
import './styles/global.css';

const el = document.getElementById('clienvy-app');
if (el) {
	createApp(App).use(i18n).mount(el);
	requestAnimationFrame(() => {
		el.classList.add('clienvy-admin--ready');
	});
}
