/**
 * Tests for pruneRawFeatureSettings() — removing a toggled tag's clauses from
 * a raw font-feature-settings value (data-feature-settings) so the toggle can
 * win over the raw value's precedence.
 *
 * Motivation: the Glyphs Panel base cell writes "swsh" 0 over a block-level
 * swash to show the plain glyph; without pruning, the popover's Swashes toggle
 * for that span was inert because the raw value always wins on rebuild.
 */

import { pruneRawFeatureSettings } from '../utils';

describe('pruneRawFeatureSettings', () => {
	test('enabling drops "tag" 0 and keeps the other clauses', () => {
		expect(pruneRawFeatureSettings('"swsh" 0, "dlig" 1', 'swsh', true)).toBe('"dlig" 1');
	});

	test('enabling drops "tag" off too', () => {
		expect(pruneRawFeatureSettings('"swsh" off, "dlig" 1', 'swsh', true)).toBe('"dlig" 1');
	});

	test('enabling keeps an indexed "on" clause', () => {
		expect(pruneRawFeatureSettings('"salt" 2, "liga" 1', 'salt', true)).toBe('"salt" 2, "liga" 1');
	});

	test('enabling keeps a plain "on" clause', () => {
		expect(pruneRawFeatureSettings('"dlig" 1', 'dlig', true)).toBe('"dlig" 1');
	});

	test('disabling drops every clause for the tag whatever its value', () => {
		expect(pruneRawFeatureSettings('"salt" 2, "liga" 1', 'salt', false)).toBe('"liga" 1');
		expect(pruneRawFeatureSettings('"swsh" 0, "dlig" 1', 'dlig', false)).toBe('"swsh" 0');
		expect(pruneRawFeatureSettings('"swsh" 0, "dlig" 1', 'swsh', false)).toBe('"dlig" 1');
	});

	test('returns an empty string when nothing remains', () => {
		expect(pruneRawFeatureSettings('"swsh" 0', 'swsh', true)).toBe('');
		expect(pruneRawFeatureSettings('"salt" 2', 'salt', false)).toBe('');
	});

	test('other tags are untouched, including tags that share a prefix', () => {
		expect(pruneRawFeatureSettings('"ss01" 1, "ss010" 1', 'ss01', false)).toBe('"ss010" 1');
	});

	test('single-quoted clauses and a bare tag (implicit 1) are handled', () => {
		expect(pruneRawFeatureSettings("'swsh' 0, 'dlig'", 'swsh', true)).toBe("'dlig'");
		expect(pruneRawFeatureSettings("'dlig'", 'dlig', true)).toBe("'dlig'");
		expect(pruneRawFeatureSettings("'dlig'", 'dlig', false)).toBe('');
	});

	test('empty, null, and missing inputs are safe', () => {
		expect(pruneRawFeatureSettings('', 'swsh', true)).toBe('');
		expect(pruneRawFeatureSettings(null, 'swsh', true)).toBe('');
		expect(pruneRawFeatureSettings(undefined, 'swsh', false)).toBe('');
		expect(pruneRawFeatureSettings('"swsh" 0', '', true)).toBe('"swsh" 0');
	});

	test('unparseable clauses are kept verbatim', () => {
		expect(pruneRawFeatureSettings('garbage, "swsh" 0', 'swsh', true)).toBe('garbage');
	});
});
