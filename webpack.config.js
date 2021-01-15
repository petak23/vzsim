// Node
const path = require('path');

// Webpack
const webpack = require("webpack");
const {merge} = require("webpack-merge");

// Other
const devMode = process.env.NODE_ENV !== "production";

// Vue
const VUE_VERSION = require("vue/package.json").version;
const VUE_LOADER_VERSION = require("vue-loader/package.json").version;

// Webpack plugins
const TerserPlugin = require("terser-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const {VueLoaderPlugin} = require("vue-loader");
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const WebpackAssetsManifest = require('webpack-assets-manifest');

// Webpack abilities
const WEBPACK_DEV_SERVER_HOST = process.env.WEBPACK_DEV_SERVER_HOST || 'localhost';
const WEBPACK_DEV_SERVER_PORT = parseInt(process.env.WEBPACK_DEV_SERVER_PORT, 10) || 8080;
const WEBPACK_DEV_SERVER_PROXY_HOST = process.env.WEBPACK_DEV_SERVER_PROXY_HOST || 'localhost';
const WEBPACK_DEV_SERVER_PROXY_PORT = parseInt(process.env.WEBPACK_DEV_SERVER_PROXY_PORT, 10) || 8000;

// Config
const ROOT_PATH = __dirname;
const CACHE_PATH = ROOT_PATH + "/temp/webpack";

var AssetsPlugin = require('assets-webpack-plugin');
 
module.exports = {
  mode: devMode ? "development" : "production",
  context: path.join(ROOT_PATH, "app/assets"),
  entry: {
    front: [path.join(ROOT_PATH, "app/assets/front/css/main.css"), path.join(ROOT_PATH, "app/assets/front/js/main.js")],
    //admin: [path.join(ROOT_PATH, "app/assets/admin/css/main.css"), path.join(ROOT_PATH, "app/assets/admin/js/main.js")]
    //texyla: [path.join(ROOT_PATH, "www/texyla/css/main.css"), path.join(ROOT_PATH, "www/texyla/texyla-init.js")]
  },
  output: {
    path: path.join(ROOT_PATH, 'www/dist'),
    //publicPath: "/dist/"
    filename: devMode ? '[name].bundle.js' : '[name].[chunkhash:8].bundle.js',
    chunkFilename: devMode ? '[name].chunk.js' : '[name].[chunkhash:8].chunk.js'
  },  
  module: {
    //noParse: /^(vue|vue-router|vuex|vuex-router-sync)$/,
		rules: [
			{
        test: /\.js$/,
        exclude: path => /node_modules/.test(path) && !/\.vue\.js/.test(path),
        loader: 'babel-loader',
        options: {
          cacheDirectory: path.join(CACHE_PATH, 'babel-loader'),
        }
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          cacheDirectory: path.join(CACHE_PATH, 'vue-loader'),
          cacheIdentifier: [
            process.env.NODE_ENV || 'development',
            webpack.version,
            VUE_VERSION,
            VUE_LOADER_VERSION,
          ].join('|')
        }
      },
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader'
        ]
      },
      {
        test: /\.svg$/,
        loader: 'raw-loader'
      },
      {
        test: /\.(png|svg|jpe?g|gif|webp|ico)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[hash:8].[ext]',
              outputPath: 'images/'
            }
          }  
        ]
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[hash:8].[ext]',
              outputPath: 'fonts/'
            }
          }
        ]
      }
    ]
  },
//  resolve: {
//    extensions: ['*', '.js', '.jsx'], //		extensions: [".js", ".vue", ".ts", ".tsx"],
//    alias: {
//      'vue$': 'vue/dist/vue.esm.js' ,
//      '@': path.resolve(__dirname, "app/assets")
//    },
//    modules: ['node_modules']
//  },
  resolve: {
    alias: {
        'vue$': 'vue/dist/vue.esm.js',
        '@': path.resolve(ROOT_PATH, 'app/assets'),
    },
    extensions: ['.js', '.vue']
  },
  plugins: [
    // enable vue-loader to use existing loader rules for other module types
		new VueLoaderPlugin(),
    
    // fix legacy jQuery plugins which depend on globals
		new webpack.ProvidePlugin({
			$: "jquery",
			jQuery: "jquery",
			"window.jQuery": "jquery",
			"window.$": "jquery",
			Popper: ["popper.js", "default"]
		}),
    
    new MiniCssExtractPlugin({
      filename: devMode ? '[name].bundle.css' : '[name].[chunkhash:8].bundle.css'
    }),
    new AssetsPlugin({ // Pre aplikaciu filename: '[name].[contenthash:8].[ext]' a prepojenie s nette
      includeManifest: 'manifest',
      path: path.join(ROOT_PATH, 'www/dist')
    }),
    
    // human webpack errors
		new FriendlyErrorsWebpackPlugin()
  ],
  devtool: '#cheap-module-eval-source-map',
  performance: {
    hints: false
  }
//  devServer: {
//    publicPath: '/dist/'
//  }
};


// ****************************
// WEBPACK DEVELOPMENT CONFIG *
// ****************************

if (process.env.NODE_ENV === 'development') {
  const development = {
    devServer: {
      host: WEBPACK_DEV_SERVER_HOST,
      port: WEBPACK_DEV_SERVER_PORT,
      disableHostCheck: true,
      contentBase: path.join(ROOT_PATH, 'www'),
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': '*'
      },
      stats: 'errors-only',
      hot: true,
      inline: true,
      proxy: {
        '/': `http://${WEBPACK_DEV_SERVER_PROXY_HOST}:${WEBPACK_DEV_SERVER_PROXY_PORT}`
      }
    }
  };

  module.exports = merge(module.exports, development);
}


// ***************************
// WEBPACK PRODUCTION CONFIG *
// ***************************

if (process.env.NODE_ENV === 'production') {
  const production = {
    devtool: 'source-map',
    optimization: {
      minimizer: [
        new TerserPlugin({
          terserOptions: {
            cache: `${CACHE_PATH}/webpack/terser`,
            parallel: true,
            ecma: 8,
            warnings: false,
            parse: {},
            compress: {},
            mangle: true, // Note `mangle.properties` is `false` by default.
            module: false,
            output: null,
            toplevel: false,
            nameCache: null,
            ie8: false,
            keep_classnames: undefined,
            keep_fnames: false,
            safari10: false
          }
        })
      ]
    },
    plugins: [
      // optimize CSS files
      new OptimizeCSSAssetsPlugin()
    ]
  };

  module.exports = merge(module.exports, production);
}


// ************************
// WEBPACK OPT-INS CONFIG *
// ************************

if (process.env.WEBPACK_REPORT === '1') {
  module.exports.plugins.push(
    new BundleAnalyzerPlugin({
      analyzerMode: 'server',
      openAnalyzer: true,
    })
  );
}