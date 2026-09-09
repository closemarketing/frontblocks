const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const scriptPath = path.resolve(__dirname, '../../assets/carousel/frontblocks-carousel.js');
const carouselScript = fs.readFileSync(scriptPath, 'utf8');

function makeElement(tag) {
	const listeners = {};
	const el = {
		tagName: (tag || 'div').toUpperCase(),
		children: [],
		classList: {
			list: [],
			add(...names) { names.forEach((n) => { if (!this.list.includes(n)) this.list.push(n); }); },
			contains(name) { return this.list.includes(name); }
		},
		style: { setProperty() {} },
		attributes: {},
		setAttribute(name, value) { this.attributes[name] = String(value); },
		getAttribute(name) { return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null; },
		appendChild(child) { this.children.push(child); child.parentNode = el; return child; },
		replaceChild(newChild, oldChild) {
			const index = this.children.indexOf(oldChild);
			if (index !== -1) this.children[index] = newChild;
			newChild.parentNode = el;
			return oldChild;
		},
		querySelectorAll() { return []; },
		addEventListener(type, cb) { (listeners[type] = listeners[type] || []).push(cb); },
		dispatch(type) { (listeners[type] || []).forEach((cb) => cb()); },
		set innerHTML(value) { this._innerHTML = value; },
		get innerHTML() { return this._innerHTML || ''; },
		set type(value) { this._type = value; }
	};
	return el;
}

/**
 * Builds a minimal environment around one `.frontblocks-carousel` item and
 * runs the carousel bootstrap script against it, returning the constructed
 * Glide config, the Glide instance's pause()/play() call log, and the
 * created wrapper (so hover/focus/click events can be dispatched on it).
 */
function createEnvironment(attrs = {}, { matchesReducedMotion = false } = {}) {
	const item = makeElement('div');
	item.classList.add('frontblocks-carousel');
	Object.entries(attrs).forEach(([name, value]) => item.setAttribute(name, value));

	const outerParent = makeElement('div');
	outerParent.appendChild(item);

	const glideCalls = { pause: 0, play: 0 };
	let glideConfig = null;
	let wrapperParentEl = null;

	function Glide(el, config) {
		wrapperParentEl = el;
		glideConfig = config;
		this.pause = () => { glideCalls.pause += 1; };
		this.play = () => { glideCalls.play += 1; };
		this.mount = () => {};
		this.on = () => {};
	}

	const documentStub = {
		querySelectorAll(selector) {
			assert.equal(selector, '.frontblocks-carousel');
			return [ item ];
		},
		createElement(tag) { return makeElement(tag); }
	};

	const windowStub = {
		innerWidth: 1200,
		screen: { availWidth: 1200 },
		matchMedia: (query) => ({ matches: matchesReducedMotion && query.includes('prefers-reduced-motion') }),
		addEventListener(type, cb) { if ('load' === type) cb(); }
	};

	const context = { document: documentStub, window: windowStub, Glide, Math };
	vm.createContext(context);
	vm.runInContext(carouselScript, context);

	return {
		glideConfig,
		glideCalls,
		wrapperParent: wrapperParentEl,
		pauseButton: wrapperParentEl.children.find((child) => child.classList.contains('glide__pause'))
	};
}

test('autoplay is passed through to Glide when the visitor has no reduced-motion preference', () => {
	const { glideConfig } = createEnvironment({ 'data-autoplay': '5000' });

	assert.equal(glideConfig.autoplay, 5000);
});

test('autoplay is forced off for visitors with prefers-reduced-motion, regardless of the configured value', () => {
	const { glideConfig } = createEnvironment({ 'data-autoplay': '5000' }, { matchesReducedMotion: true });

	assert.equal(glideConfig.autoplay, false);
});

test('no pause button is rendered when autoplay is off', () => {
	const { pauseButton } = createEnvironment({ 'data-autoplay': '' });

	assert.equal(pauseButton, undefined);
});

test('no pause button is rendered for a reduced-motion visitor even if autoplay was configured', () => {
	const { pauseButton } = createEnvironment({ 'data-autoplay': '5000' }, { matchesReducedMotion: true });

	assert.equal(pauseButton, undefined);
});

test('an accessible pause button is rendered whenever autoplay is actually running', () => {
	const { pauseButton } = createEnvironment({ 'data-autoplay': '5000' });

	assert.ok(pauseButton);
	assert.equal(pauseButton.getAttribute('aria-pressed'), 'false');
	assert.equal(pauseButton.getAttribute('aria-label'), 'Pause automatic slideshow');
});

test('clicking the pause button pauses Glide and flips its own label/state', () => {
	const { pauseButton, glideCalls } = createEnvironment({ 'data-autoplay': '5000' });

	pauseButton.dispatch('click');

	assert.equal(glideCalls.pause, 1);
	assert.equal(pauseButton.getAttribute('aria-pressed'), 'true');
	assert.equal(pauseButton.getAttribute('aria-label'), 'Play automatic slideshow');

	pauseButton.dispatch('click');

	assert.equal(glideCalls.play, 1);
	assert.equal(pauseButton.getAttribute('aria-pressed'), 'false');
	assert.equal(pauseButton.getAttribute('aria-label'), 'Pause automatic slideshow');
});

test('keyboard/AT focus inside the carousel pauses autoplay and resumes it on blur, independent of the pause button state', () => {
	const { wrapperParent, glideCalls } = createEnvironment({ 'data-autoplay': '5000', 'data-pause-on-hover': 'true' });

	wrapperParent.dispatch('focusin');
	assert.equal(glideCalls.pause, 1);

	wrapperParent.dispatch('focusout');
	assert.equal(glideCalls.play, 1);
});

test('hover/focus auto-pause is skipped entirely when the pause-on-hover attribute is disabled', () => {
	const { wrapperParent, glideCalls } = createEnvironment({ 'data-autoplay': '5000', 'data-pause-on-hover': 'false' });

	wrapperParent.dispatch('mouseenter');
	wrapperParent.dispatch('focusin');

	assert.equal(glideCalls.pause, 0);
});

test('a manual pause survives the mouse leaving the carousel — the two pause reasons don\'t fight each other', () => {
	const { wrapperParent, pauseButton, glideCalls } = createEnvironment({ 'data-autoplay': '5000' });

	wrapperParent.dispatch('mouseenter');
	pauseButton.dispatch('click');
	assert.ok(glideCalls.pause > 0);

	wrapperParent.dispatch('mouseleave');

	assert.equal(glideCalls.play, 0, 'Autoplay must stay paused: the manual pause reason is still active even though hover ended.');
});
