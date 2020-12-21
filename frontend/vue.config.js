module.exports = {
	devServer: {
		proxy: {
			'/api': {
				target: 'https://admin.kang-web.tk/backend/',
				ws: true,
				changeOrigin: true
			},
		}
	},
	outputDir: 'production',
	productionSourceMap: false,
	pluginOptions: {
		i18n: {
			locale: 'zh-tw',
			fallbackLocale: 'zh-tw',
			localeDir: 'locales',
			enableInSFC: true
		}
		// autoRouting: {
		//   chunkNamePrefix: 'page-'
		// }
	},
	pages: {
		index: {
			entry: 'src/main.js',
			template: 'public/index.html',
			filename: 'index.html',
			title: '後台管理系統',
			chunks: ['chunk-vendors', 'chunk-common', 'index']
		},
	}
}
