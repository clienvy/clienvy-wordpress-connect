import nl from '../../assets/language/nl-NL.json';

const locales = {
	'nl-NL': nl,
};

let current = 'nl-NL';

export function t(key, replacements = {}) {
	const dict = locales[current] || {};
	const value = key.split('.').reduce((acc, k) => (acc && typeof acc === 'object' ? acc[k] : undefined), dict);
	if (typeof value !== 'string') return key;
	return Object.entries(replacements).reduce(
		(out, [from, to]) => out.replaceAll(`{${from}}`, String(to)),
		value
	);
}

export function setLocale(locale) {
	if (locales[locale]) current = locale;
}

export default {
	install(app) {
		app.config.globalProperties.$t = t;
	},
};
