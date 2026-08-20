import {resolve} from 'path';
import {defineConfig} from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
	plugins: [vue()],
	publicDir: false,
	build: {
		emptyOutDir: false,
		lib: {
			entry: resolve(__dirname, 'resources/js/main.js'),
			fileName: 'toggleRequiredMetadata',
			formats: ['iife'],
			name: 'ToggleRequiredMetadataPlugin',
		},
		outDir: resolve(__dirname, 'public/build'),
		rollupOptions: {
			external: ['vue'],
			output: {
				globals: {
					vue: 'pkp.modules.vue',
				},
			},
		},
		target: 'es2016',
	},
});
