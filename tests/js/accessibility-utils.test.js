const assert = require('node:assert/strict');
const test = require('node:test');

const a11y = require('../../assets/accessibility/frontblocks-a11y-utils.js');

test('getContrastRatio computes the known WCAG ratio for black on white', () => {
	assert.equal(a11y.getContrastRatio('#000000', '#ffffff'), 21);
});

test('getContrastRatio is symmetric regardless of argument order', () => {
	const ratioA = a11y.getContrastRatio('#777777', '#ffffff');
	const ratioB = a11y.getContrastRatio('#ffffff', '#777777');
	assert.equal(ratioA, ratioB);
});

test('getContrastRatio expands 3-digit hex shorthand', () => {
	assert.equal(a11y.getContrastRatio('#000', '#fff'), 21);
});

test('getContrastRatio parses rgb()/rgba() colors', () => {
	assert.equal(a11y.getContrastRatio('rgb(0, 0, 0)', 'rgba(255, 255, 255, 0.45)'), 21);
});

test('getContrastRatio returns null for an unparsable color', () => {
	assert.equal(a11y.getContrastRatio('not-a-color', '#ffffff'), null);
	assert.equal(a11y.getContrastRatio('#ffffff', undefined), null);
});

test('checkControlContrast is not applicable when either color is missing', () => {
	assert.deepEqual(a11y.checkControlContrast('', '#ffffff'), { applicable: false, ratio: null, warn: false });
	assert.deepEqual(a11y.checkControlContrast('#ffffff', undefined), { applicable: false, ratio: null, warn: false });
});

test('checkControlContrast warns below the 3:1 non-text contrast threshold', () => {
	// #999 on #fff is roughly 2.85:1 — below the 3:1 non-text minimum.
	const result = a11y.checkControlContrast('#999999', '#ffffff');
	assert.equal(result.applicable, true);
	assert.equal(result.warn, true);
});

test('checkControlContrast does not warn when the ratio meets 3:1', () => {
	const result = a11y.checkControlContrast('#000000', '#ffffff');
	assert.equal(result.applicable, true);
	assert.equal(result.warn, false);
});

test('checkAutoplayPauseControl is not applicable when autoplay is off', () => {
	assert.deepEqual(a11y.checkAutoplayPauseControl('', false), { applicable: false, warn: false });
	assert.deepEqual(a11y.checkAutoplayPauseControl('0', false), { applicable: false, warn: false });
});

test('checkAutoplayPauseControl warns when autoplay is set without a pause control', () => {
	assert.deepEqual(a11y.checkAutoplayPauseControl('6', false), { applicable: true, warn: true });
});

test('checkAutoplayPauseControl does not warn when a pause control is enabled', () => {
	assert.deepEqual(a11y.checkAutoplayPauseControl('6', true), { applicable: true, warn: false });
});

test('checkAutoplayTiming is not applicable when autoplay is off', () => {
	assert.equal(a11y.checkAutoplayTiming('').applicable, false);
});

test('checkAutoplayTiming warns on unsafe/very short intervals', () => {
	const result = a11y.checkAutoplayTiming('2');
	assert.equal(result.applicable, true);
	assert.equal(result.warn, true);
	assert.equal(result.seconds, 2);
});

test('checkAutoplayTiming does not warn at or above the 5-second minimum', () => {
	assert.equal(a11y.checkAutoplayTiming('5').warn, false);
	assert.equal(a11y.checkAutoplayTiming('8').warn, false);
});

test('checkImageLabel is not applicable when no image is set', () => {
	assert.deepEqual(a11y.checkImageLabel('', ''), { applicable: false, warn: false });
	assert.deepEqual(a11y.checkImageLabel(undefined, 'Before'), { applicable: false, warn: false });
});

test('checkImageLabel warns when the image is set but the label is empty or whitespace', () => {
	assert.deepEqual(a11y.checkImageLabel('https://example.com/a.jpg', ''), { applicable: true, warn: true });
	assert.deepEqual(a11y.checkImageLabel('https://example.com/a.jpg', '   '), { applicable: true, warn: true });
});

test('checkImageLabel does not warn when the image has a non-empty label', () => {
	assert.deepEqual(a11y.checkImageLabel('https://example.com/a.jpg', 'Before renovation'), { applicable: true, warn: false });
});
