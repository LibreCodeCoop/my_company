module.exports = {
	extends: [
		'@nextcloud',
	],
	globals: {
		// @nextcloud/webpack-vue-config globals
		appName: 'readonly',
		appVersion: 'readonly',
	},
	rules: {
		// production only
		'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
		'vue/no-unused-components': process.env.NODE_ENV === 'production' ? 'error' : 'warn',

		// Project rules
		'import/newline-after-import': 'warn',
		'import/no-named-as-default-member': 'off',
		'import/order': [
			'warn',
			{
				groups: ['builtin', 'external', 'internal', ['parent', 'sibling', 'index'], 'unknown'],
				pathGroups: [
					{
						// group all style imports at the end
						pattern: '{*.css,*.scss}',
						patternOptions: { matchBase: true },
						group: 'unknown',
						position: 'after',
					},
					{
						// group @nextcloud imports
						pattern: '@nextcloud/{!(vue),!(vue)/**}',
						group: 'external',
						position: 'after',
					},
					{
						// group @nextcloud/vue imports
						pattern: '{@nextcloud/vue,@nextcloud/vue/**}',
						group: 'external',
						position: 'after',
					},
					{
						// group project components
						pattern: '*.vue',
						patternOptions: { matchBase: true },
						group: 'external',
						position: 'after',
					},
				],
				pathGroupsExcludedImportTypes: ['@nextcloud'],
				'newlines-between': 'always',
				alphabetize: {
					order: 'asc',
					caseInsensitive: true,
				},
				warnOnUnassignedImports: true,
			},
		],
	},
	// Prepare for Vue 3 Migration
	'vue/no-deprecated-data-object-declaration': 'warn',
	'vue/no-deprecated-events-api': 'warn',
	'vue/no-deprecated-filter': 'warn',
	'vue/no-deprecated-functional-template': 'warn',
	'vue/no-deprecated-html-element-is': 'warn',
	'vue/no-deprecated-props-default-this': 'warn',
	'vue/no-deprecated-router-link-tag-prop': 'warn',
	'vue/no-deprecated-scope-attribute': 'warn',
	'vue/no-deprecated-slot-attribute': 'warn',
	'vue/no-deprecated-slot-scope-attribute': 'warn',
	'vue/no-deprecated-v-is': 'warn',
	'vue/no-deprecated-v-on-number-modifiers': 'warn',
	'vue/require-explicit-emits': 'warn',
}
