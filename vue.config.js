const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    proxy: {
      '/project_login/php_api': {
        target: 'http://localhost',
        changeOrigin: true
      }
    }
  }
})
