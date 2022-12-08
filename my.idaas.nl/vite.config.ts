import { defineConfig } from 'vite'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'
import vue from '@vitejs/plugin-vue2'
import path from 'path'

export default defineConfig({
	server: {
		host: '0.0.0.0',
		hmr: {
			path: 'hmr'
		}
	},
	resolve:{
		alias:{
		  '@' : path.resolve(__dirname, './src')
		},
	  },	
	plugins: [
		vue(),
	],
})
