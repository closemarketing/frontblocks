/**
 * Custom webpack config to set the source directory for wp-scripts.
 *
 * @author Closetechnology
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	context: path.resolve( __dirname, 'includes/blocks/content-link/src' ),
	entry: {
		index: path.resolve( __dirname, 'includes/blocks/content-link/src/index.js' ),
	},
	output: {
		path: path.resolve( __dirname, 'includes/blocks/content-link/build' ),
		filename: 'index.js',
	},
};
