/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./includes/Admin/**/*.php',
		'./includes/GoogleSignIn/**/*.php',
		'./assets/admin/**/*.{js,jsx}',
	],
	theme: {
		extend: {
			colors: {
				primary: {
					50: '#f0f1fe',
					100: '#e4e6fd',
					200: '#cdd0fb',
					300: '#acb0f8',
					400: '#8a8ff5',
					500: '#687df9',
					600: '#5565ed',
					700: '#4651d9',
					800: '#3941af',
					900: '#323a8a',
				},
			},
		},
	},
	plugins: [],
	// Prefix for Tailwind classes to avoid conflicts with WordPress admin.
	prefix: 'tw',
	important: '.frbl-settings-wrapper',
};
