const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

// This test builds its own tiny DOM shim (below) instead of pulling in a
// real DOM implementation, matching this repo's existing frontend JS tests
// (see tests/js/cookie-notice-injection.test.js).
const scriptPath = path.resolve(__dirname, '../../assets/column-link/frontblocks-column-link.js');
const columnLinkScript = fs.readFileSync(scriptPath, 'utf8');

/**
 * Minimal fake DOM element supporting exactly what the script under test
 * relies on: attributes, closest(), contains(), and click/keydown dispatch
 * via the click()/keydown() helpers the tests call directly.
 */
function createElement(tag, attrs = {}, parent = null) {
	const el = {
		tagName: tag.toUpperCase(),
		attrs: { ...attrs },
		parent,
		children: [],
		getAttribute(name) {
			return Object.prototype.hasOwnProperty.call(this.attrs, name) ? this.attrs[name] : null;
		},
		matches(selector) {
			return selector.split(',').map((s) => s.trim()).some((single) => matchesSingle(this, single));
		},
		closest(selector) {
			let node = this;
			while (node) {
				if (node.matches && node.matches(selector)) {
					return node;
				}
				node = node.parent;
			}
			return null;
		},
		contains(node) {
			let current = node;
			while (current) {
				if (current === this) {
					return true;
				}
				current = current.parent;
			}
			return false;
		}
	};

	if (parent) {
		parent.children.push(el);
	}

	return el;
}

function matchesSingle(el, selector) {
	selector = selector.trim();

	const attrMatch = selector.match(/^\[([a-zA-Z0-9-]+)(?:="([^"]*)")?\]$/);
	if (attrMatch) {
		const [, name, value] = attrMatch;
		if (undefined === value) {
			return null !== el.getAttribute(name);
		}
		return el.getAttribute(name) === value;
	}

	if ('[contenteditable="true"]' === selector) {
		return 'true' === el.getAttribute('contenteditable');
	}

	return el.tagName === selector.toUpperCase();
}

function createEnvironment() {
	const listeners = { click: null, keydown: null };
	const document = {
		addEventListener(event, callback) {
			listeners[event] = callback;
		}
	};
	const opened = [];
	const window = {
		location: { href: '' },
		open(...args) {
			opened.push(args);
		}
	};

	vm.runInNewContext(columnLinkScript, { document, window });

	return { listeners, opened, window };
}

test('clicking the column background navigates to the configured URL', () => {
	const { listeners, window } = createEnvironment();
	const column = createElement('div', { 'data-frbl-column-link-url': 'https://example.com/landing' });
	const paragraph = createElement('p', {}, column);

	listeners.click({ target: paragraph });

	assert.equal(window.location.href, 'https://example.com/landing');
});

test('clicking a nested link inside the column does not hijack the click', () => {
	const { listeners, window } = createEnvironment();
	const column = createElement('div', { 'data-frbl-column-link-url': 'https://example.com/landing' });
	const link = createElement('a', { href: 'https://example.com/other' }, column);

	listeners.click({ target: link });

	assert.equal(window.location.href, '');
});

test('clicking a nested button inside the column does not hijack the click', () => {
	const { listeners, window } = createEnvironment();
	const column = createElement('div', { 'data-frbl-column-link-url': 'https://example.com/landing' });
	const button = createElement('button', {}, column);

	listeners.click({ target: button });

	assert.equal(window.location.href, '');
});

test('a click outside any linked column is ignored', () => {
	const { listeners, window } = createEnvironment();
	const unrelated = createElement('div');

	listeners.click({ target: unrelated });

	assert.equal(window.location.href, '');
});

test('opens in a new tab with noopener/noreferrer when configured', () => {
	const { listeners, opened } = createEnvironment();
	const column = createElement('div', {
		'data-frbl-column-link-url': 'https://example.com/landing',
		'data-frbl-column-link-target': '_blank'
	});

	listeners.click({ target: column });

	assert.equal(opened.length, 1);
	assert.deepEqual(opened[0], ['https://example.com/landing', '_blank', 'noopener,noreferrer']);
});

test('pressing Enter while the column itself is focused navigates', () => {
	const { listeners, window } = createEnvironment();
	const column = createElement('div', { 'data-frbl-column-link-url': 'https://example.com/landing' });
	let prevented = false;

	listeners.keydown({ key: 'Enter', target: column, preventDefault: () => { prevented = true; } });

	assert.equal(window.location.href, 'https://example.com/landing');
	assert.equal(prevented, true);
});

test('pressing Enter while a nested control is focused does not navigate', () => {
	const { listeners, window } = createEnvironment();
	const column = createElement('div', { 'data-frbl-column-link-url': 'https://example.com/landing' });
	const input = createElement('input', {}, column);

	listeners.keydown({ key: 'Enter', target: input, preventDefault: () => {} });

	assert.equal(window.location.href, '');
});

test('pressing an unrelated key does nothing', () => {
	const { listeners, window } = createEnvironment();
	const column = createElement('div', { 'data-frbl-column-link-url': 'https://example.com/landing' });

	listeners.keydown({ key: 'Tab', target: column, preventDefault: () => {} });

	assert.equal(window.location.href, '');
});
