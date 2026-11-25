const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const carouselScript = fs.readFileSync(path.resolve(__dirname, '../../assets/carousel/frontblocks-carousel.js'), 'utf8');

function makeElement() {
	const el = {
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
		addEventListener() {},
		querySelectorAll() { return []; }
	};
	return el;
}

function makeList() {
	const list = makeElement();
	list.classList.add('frontblocks-carousel');
	list.appendChild(makeElement());
	list.parentNode = makeElement();
	return list;
}

/**
 * Runs the script in a page that is still loading, so nothing starts until init() is called.
 */
function load({ readyState = 'loading', lists = [] } = {}) {
	const mounted = [];
	const loadListeners = [];

	function Glide(el) {
		mounted.push(el);
		this.mount = () => {};
		this.on = () => {};
	}

	const documentStub = {
		readyState,
		querySelectorAll: () => lists,
		createElement: makeElement
	};
	const windowStub = {
		innerWidth: 1200,
		screen: { availWidth: 1200 },
		matchMedia: () => ({ matches: false }),
		addEventListener(type, cb) { if ('load' === type) loadListeners.push(cb); }
	};

	const context = { document: documentStub, window: windowStub, Glide, Math };
	vm.createContext(context);
	vm.runInContext(carouselScript, context);

	return { api: windowStub.frontblocksCarousel, mounted, loadListeners };
}

test('exposes window.frontblocksCarousel.init', () => {
	const { api } = load();

	assert.equal(typeof api.init, 'function');
});

test('starts the lists on window load, as before', () => {
	const { mounted, loadListeners } = load({ lists: [ makeList() ] });

	assert.equal(mounted.length, 0);
	loadListeners.forEach((cb) => cb());
	assert.equal(mounted.length, 1);
});

test('starts at once when the script runs after the window has loaded', () => {
	const { mounted } = load({ readyState: 'complete', lists: [ makeList() ] });

	assert.equal(mounted.length, 1);
});

test('init starts lists added after load and never mounts one twice', () => {
	const late = makeList();
	const { api, mounted } = load({ readyState: 'complete', lists: [] });
	const container = makeElement();
	container.querySelectorAll = () => [ late ];

	api.init(container);
	api.init(container);

	assert.equal(mounted.length, 1);
	assert.ok(late.classList.contains('glide__slides'));
});

test('init(container) only looks inside the container', () => {
	const outside = makeList();
	const inside = makeList();
	const { api, mounted } = load({ lists: [ outside ] });
	const container = makeElement();
	container.querySelectorAll = () => [ inside ];

	api.init(container);

	assert.equal(mounted.length, 1);
	assert.ok(!outside.classList.contains('glide__slides'));
});
