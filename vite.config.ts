import { defineConfig } from 'vite'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'
import laravel from 'vite-plugin-laravel'
import vue from '@vitejs/plugin-vue2'
import path from 'path'

export default defineConfig({
	server: {
		host: '0.0.0.0'
	},
	resolve:{
		alias:{
		  '@' : path.resolve(__dirname, './frontend/src')
		},
	  },	
	plugins: [
		vue(),
		laravel({
			postcss: [
				tailwindcss(),
				autoprefixer(),
			]
		}),
	],
})
