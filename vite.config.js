import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig(({ mode }) => ({
	plugins: [vue()],
	define: {
		'process.env.NODE_ENV': JSON.stringify(mode === 'development' ? 'development' : 'production'),
		__VUE_OPTIONS_API__: 'false',
		__VUE_PROD_DEVTOOLS__: 'false',
		__VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
	},
	build: {
		lib: {
			entry: resolve(__dirname, 'src/admin/main.js'),
			name: 'ClienvyAdmin',
			formats: ['iife'],
			fileName: () => 'admin.js',
		},
		outDir: 'assets/dist',
		emptyOutDir: true,
		cssCodeSplit: false,
		rollupOptions: {
			output: {
				assetFileNames: (asset) => {
					if (asset.name && asset.name.endsWith('.css')) return 'admin.css';
					return '[name][extname]';
				},
			},
		},
	},
}));
