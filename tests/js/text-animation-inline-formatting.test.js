const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const scriptPath = path.resolve(__dirname, '../../assets/text-animation/frontblocks-text-animation-frontend.js');
const frontendScript = fs.readFileSync(scriptPath, 'utf8');

function makeElement({ tagName = 'A' } = {}) {
	return { tagName };
}

function runInit(elements) {
	const document = {
		readyState: 'complete',
		addEventListener() {},
		querySelectorAll(selector) {
			assert.equal(selector, '.frbl-text-animation[data-animation]');
			return elements;
		}
	};
	const handledElements = [];
	const window = {
		FrblAnimations: {
			'fade-in': (el) => handledElements.push(el)
		}
	};
	const context = { document, window };
	vm.createContext(context);
	vm.runInContext(frontendScript, context);

	return { handledElements, window };
}

function frblTextAnimationElement({ children = [] } = {}) {
	return {
		tagName: 'P',
		children,
		getAttribute(name) {
			if ('data-animation' === name) return 'fade-in';
			if ('data-animation-loop' === name) return null;
			return null;
		}
	};
}

test('an element with no inline formatting is handed to its animation handler', () => {
	const el = frblTextAnimationElement();

	const { handledElements } = runInit([ el ]);

	assert.deepEqual(handledElements, [ el ]);
});

test('an element containing a link is skipped, preserving the link instead of destroying it', () => {
	const el = frblTextAnimationElement({ children: [ makeElement({ tagName: 'A' }) ] });

	const { handledElements } = runInit([ el ]);

	assert.deepEqual(handledElements, [], 'The character-splitting handler must never run on content with a link — it would discard the <a> entirely.');
});

test('an element containing bold/italic/etc. formatting is also skipped', () => {
	[ 'STRONG', 'B', 'EM', 'I', 'MARK', 'CODE', 'ABBR', 'SUB', 'SUP', 'SPAN', 'KBD' ].forEach((tagName) => {
		const el = frblTextAnimationElement({ children: [ makeElement({ tagName }) ] });

		const { handledElements } = runInit([ el ]);

		assert.deepEqual(handledElements, [], `Content with a <${tagName}> must not be handed to the character-splitting handler.`);
	});
});

test('FrblHasInlineFormatting only checks direct children, matching FrblWordWrap\'s own :scope assumption', () => {
	const { window } = runInit([]);

	assert.equal(window.FrblHasInlineFormatting({ children: [] }), false);
	assert.equal(window.FrblHasInlineFormatting({ children: [ makeElement({ tagName: 'A' }) ] }), true);
});
