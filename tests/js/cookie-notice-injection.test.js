const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const scriptPath = path.resolve(__dirname, '../../assets/cookie-notice/frontblocks-cookie-notice.js');
const cookieNoticeScript = fs.readFileSync(scriptPath, 'utf8');

function createEnvironment(existingOaiq) {
	const scripts = [];
	const listeners = {};
	const acceptListeners = {};
	const banner = {
		classList: {
			add() {},
			contains() { return false; },
			remove() {}
		},
		parentNode: { removeChild() {} },
		querySelector(selector) {
			if (selector === '[data-frbl-cookie-action="accept"]') {
				return {
					addEventListener(event, callback) {
						acceptListeners[event] = callback;
					}
				};
			}
			return null;
		},
		querySelectorAll() { return []; },
		style: {}
	};
	const document = {
		activeElement: null,
		body: { classList: { add() {}, remove() {} } },
		cookie: '',
		head: { appendChild(script) { scripts.push(script); } },
		readyState: 'loading',
		addEventListener(event, callback) { listeners[event] = callback; },
		createElement() { return {}; },
		dispatchEvent() {},
		getElementById() { return banner; },
		getElementsByTagName() { return []; },
		removeEventListener() {}
	};
	const window = {
		location: { protocol: 'https:' },
		setTimeout(callback) { callback(); }
	};
	if (existingOaiq) {
		window.oaiq = existingOaiq;
	}
	const context = {
		Array,
		CustomEvent: function () {},
		Date,
		FormData: function () { this.append = function () {}; },
		decodeURIComponent,
		document,
		encodeURIComponent,
		fetch() {
			return Promise.resolve({
				json() {
					return Promise.resolve({
						success: true,
						data: {
							gtmId: '',
							ga4Id: '',
							trackingIntegrations: [ { type: 'openai_chatgpt_ads', id: 'TestChatGPTPixelId1234' }
							]
						}
					});
				}
			});
		},
		frblCookieNotice: {
			ajaxUrl: 'https://example.test/wp-admin/admin-ajax.php',
			cookieName: 'frbl_cookie_consent',
			cookiePath: '/',
			expirationDays: 365
		},
		window
	};
	vm.runInNewContext(cookieNoticeScript, context);
	listeners.DOMContentLoaded();

	return { acceptListeners, scripts, window };
}

test('does not load ChatGPT Ads before consent and loads it after acceptance', async () => {
	const environment = createEnvironment();

	assert.equal(environment.scripts.length, 0);
	environment.acceptListeners.click();
	await new Promise((resolve) => setImmediate(resolve));
	await new Promise((resolve) => setImmediate(resolve));

	assert.equal(environment.scripts.length, 1);
	assert.equal(environment.scripts[0].src, 'https://bzrcdn.openai.com/sdk/oaiq.min.js');
	assert.equal(environment.window.oaiq.q[0][0], 'init');
	assert.equal(environment.window.oaiq.q[0][1].pixelId, 'TestChatGPTPixelId1234');
	assert.equal(environment.window.oaiq.q[0][1].debug, true);
});

test('initializes ChatGPT Ads when oaiq already exists', () => {
	const calls = [];
	const environment = createEnvironment(function () {
		calls.push(Array.from(arguments));
	});

	environment.window.frblCookieNoticeInject('', '', [
		{ type: 'openai_chatgpt_ads', id: 'TestChatGPTPixelId1234' }
	]);

	assert.equal(environment.scripts.length, 0);
	assert.equal(calls.length, 1);
	assert.equal(calls[0][0], 'init');
	assert.equal(calls[0][1].pixelId, 'TestChatGPTPixelId1234');
	assert.equal(calls[0][1].debug, true);
});
