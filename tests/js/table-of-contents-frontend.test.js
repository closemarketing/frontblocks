const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const scriptPath = path.resolve(__dirname, '../../assets/table-of-contents/frontblocks-toc-frontend.js');
const tocScript = fs.readFileSync(scriptPath, 'utf8');

function makeLink(href) {
	const listeners = {};
	return {
		attributes: { href },
		getAttribute(name) { return this.attributes[name]; },
		setAttribute(name, value) { this.attributes[name] = value; },
		removeAttribute(name) { delete this.attributes[name]; },
		addEventListener(type, cb) { (listeners[type] = listeners[type] || []).push(cb); },
		dispatch(type, event) { (listeners[type] || []).forEach((cb) => cb(event || { preventDefault() {} })); }
	};
}

function makeHeading(id) {
	return {
		id,
		scrollCalls: [],
		focusCalls: [],
		scrollIntoView(options) { this.scrollCalls.push(options); },
		focus(options) { this.focusCalls.push(options); }
	};
}

/**
 * Builds a minimal environment: one `.frbl-toc` root with a set of links,
 * a `document.getElementById` map resolving those hrefs to heading mocks,
 * and a captured IntersectionObserver so scrollspy behavior can be driven
 * manually.
 */
function createEnvironment({ links, reducedMotion = false } = {}) {
	const tocLinks = links.map((href) => makeLink(href));
	const headingsById = {};
	links.forEach((href) => {
		const id = href.slice(1);
		if ('missing' !== id) headingsById[id] = makeHeading(id);
	});

	const toc = {
		querySelectorAll(selector) {
			assert.equal(selector, '.frbl-toc__link');
			return tocLinks;
		}
	};

	let observerInstance = null;
	function FakeIntersectionObserver(callback) {
		this.callback = callback;
		this.observed = [];
		observerInstance = this;
	}
	FakeIntersectionObserver.prototype.observe = function (el) { this.observed.push(el); };

	let pushedHash = null;
	const documentStub = {
		readyState: 'complete',
		addEventListener() {},
		querySelectorAll(selector) {
			assert.equal(selector, '.frbl-toc');
			return [ toc ];
		},
		getElementById(id) { return headingsById[id] || null; }
	};

	const windowStub = {
		matchMedia: (query) => ({ matches: reducedMotion && query.includes('prefers-reduced-motion') }),
		history: { pushState: (state, title, hash) => { pushedHash = hash; } }
	};

	const context = {
		document: documentStub,
		window: windowStub,
		IntersectionObserver: FakeIntersectionObserver
	};
	vm.createContext(context);
	vm.runInContext(tocScript, context);

	return {
		tocLinks,
		headingsById,
		getObserver: () => observerInstance,
		getPushedHash: () => pushedHash
	};
}

test('clicking a link scrolls to and focuses its target heading', () => {
	const { tocLinks, headingsById, getPushedHash } = createEnvironment({ links: [ '#section-one' ] });
	let prevented = false;

	tocLinks[0].dispatch('click', { preventDefault: () => { prevented = true; } });

	assert.equal(prevented, true);
	assert.equal(getPushedHash(), '#section-one');
	assert.equal(headingsById['section-one'].scrollCalls[0].behavior, 'smooth');
	assert.equal(headingsById['section-one'].scrollCalls[0].block, 'start');
	assert.equal(headingsById['section-one'].focusCalls[0].preventScroll, true);
});

test('scrolling is instant (not smooth) for visitors with prefers-reduced-motion', () => {
	const { tocLinks, headingsById } = createEnvironment({ links: [ '#section-one' ], reducedMotion: true });

	tocLinks[0].dispatch('click');

	assert.equal(headingsById['section-one'].scrollCalls[0].behavior, 'auto');
});

test('clicking a link with no matching heading in the document does nothing and does not throw', () => {
	const { tocLinks } = createEnvironment({ links: [ '#missing' ] });
	let prevented = false;

	assert.doesNotThrow(() => {
		tocLinks[0].dispatch('click', { preventDefault: () => { prevented = true; } });
	});
	assert.equal(prevented, false, 'preventDefault must not be called for a target that does not exist — let the browser navigate normally.');
});

test('the intersection observer marks the first visible heading\'s link as the current location', () => {
	const { tocLinks, headingsById, getObserver } = createEnvironment({ links: [ '#one', '#two' ] });
	const observer = getObserver();

	observer.callback([
		{ isIntersecting: true, target: headingsById.two, boundingClientRect: { top: 50 } },
		{ isIntersecting: true, target: headingsById.one, boundingClientRect: { top: 10 } }
	]);

	assert.equal(tocLinks[0].getAttribute('aria-current'), 'location');
	assert.equal(tocLinks[1].getAttribute('aria-current'), undefined);
});

test('the previous active link loses aria-current when a different section becomes active', () => {
	const { tocLinks, headingsById, getObserver } = createEnvironment({ links: [ '#one', '#two' ] });
	const observer = getObserver();

	observer.callback([ { isIntersecting: true, target: headingsById.one, boundingClientRect: { top: 10 } } ]);
	assert.equal(tocLinks[0].getAttribute('aria-current'), 'location');

	observer.callback([ { isIntersecting: true, target: headingsById.two, boundingClientRect: { top: 10 } } ]);

	assert.equal(tocLinks[0].getAttribute('aria-current'), undefined);
	assert.equal(tocLinks[1].getAttribute('aria-current'), 'location');
});

test('a heading that is no longer intersecting does not get marked active', () => {
	const { tocLinks, headingsById, getObserver } = createEnvironment({ links: [ '#one' ] });
	const observer = getObserver();

	observer.callback([ { isIntersecting: false, target: headingsById.one, boundingClientRect: { top: 10 } } ]);

	assert.equal(tocLinks[0].getAttribute('aria-current'), undefined);
});
