const fs = require('fs');

module.exports = {

  baseUrl: '/',

  devServer: {

    host: '0.0.0.0',

    headers: {
      'Access-Control-Allow-Origin': '*'
    },

    https: {
      key: fs.existsSync('/tmp/ssl/server.key') ? fs.readFileSync('/tmp/ssl/server.key') : null,
      cert: fs.existsSync('/tmp/ssl/server.crt') ? fs.readFileSync('/tmp/ssl/server.crt') : null,
      ca: fs.existsSync('/tmp/ssl/ca.pem') ? fs.readFileSync('/tmp/ssl/ca.pem') : null,
    },

    public: 'static.idaas.tst:8080',
    publicPath: '/',
    disableHostCheck: true,

    historyApiFallback: {
      rewrites: [{
          from: /\/admin/,
          to: '/admin.html'
        },
        {
          from: /./,
          to: '/index.html'
        },
      ]
    }

  },

  outputDir: '../public',

  pages: {
    admin: {
      // entry for the page
      entry: 'src/admin/main.js',
      // the source template
      template: '../resources/views/admin.blade.template.php',
      // output file
      filename: '../resources/views/admin.blade.php',
    },

    login: {
      // entry for the page
      entry: 'src/login/app.js',
      // the source template
      template: '../resources/views/login.blade.template.php',
      // output file
      filename: '../resources/views/login.blade.php',
    }

  }

}