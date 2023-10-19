import { defineConfig } from 'vite'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'
import laravel from 'vite-plugin-laravel'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
	server: {
		host: '0.0.0.0'
	},
	resolve: {
		alias: {
			'@': path.resolve(__dirname, './frontend/src'),
			vue: '@vue/compat'
		},
	},
	plugins: [
		vue({
			template: {
				compilerOptions: {
					compatConfig: {
						MODE: 3
					}
				}
			}
		}),
		laravel({
			postcss: [
				tailwindcss(),
				autoprefixer(),
			]
		}),
	],
})
