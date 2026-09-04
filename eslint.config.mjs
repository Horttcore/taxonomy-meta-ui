import wordpress from '@wordpress/eslint-plugin';
import globals from 'globals';

export default [
	...wordpress.configs.esnext,
	{
		files: [ 'scripts/**/*.js' ],
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.jquery,
				jQuery: 'readonly',
				taxonomyMetaUI: 'readonly',
			},
		},
	},
	{
		ignores: [ 'node_modules/**', 'vendor/**' ],
	},
];
