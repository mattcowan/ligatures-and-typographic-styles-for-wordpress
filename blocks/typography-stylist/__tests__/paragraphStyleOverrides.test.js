/**
 * Tests for the selection-scoped paragraph style conflict check and strip:
 * countParagraphStyleConflicts() decides whether to ask the author, and
 * stripParagraphStyleOverrides() makes the style's CSS class win afterwards.
 *
 * Motivation (2026-09-09): applying a style to the headline "Why the
 * flourishes disappear" styled every letter except the first, which sat in
 * its own span with an inline font and swash — inline declarations beat the
 * style's class in the cascade.
 */

import { countParagraphStyleConflicts, stripParagraphStyleOverrides } from '../utils';

const FIRST_LETTER = '<span class="typost-styled" data-font-id="37" data-features="swsh" style="font-feature-settings: &quot;swsh&quot; 1; font-family: var(--font-37)">W</span>hy the flourishes disappear';

describe('countParagraphStyleConflicts', () => {
	test('counts a styled span inside the selection', () => {
		expect(countParagraphStyleConflicts(FIRST_LETTER, 0, 28)).toBe(1);
	});

	test('zero for plain text, an empty range, or empty content', () => {
		expect(countParagraphStyleConflicts('Why the flourishes disappear', 0, 28)).toBe(0);
		expect(countParagraphStyleConflicts(FIRST_LETTER, 5, 5)).toBe(0);
		expect(countParagraphStyleConflicts('', 0, 5)).toBe(0);
	});

	test('a span that strictly contains the selection is not a conflict (the wrapper goes inside it)', () => {
		const html = '<span class="typost-styled" data-font-id="37" style="font-family: var(--font-37)">Why the flourishes</span> disappear';
		expect(countParagraphStyleConflicts(html, 4, 7)).toBe(0);
	});

	test('a span equal to the selection IS a conflict (merge case keeps its inline styling)', () => {
		const html = '<span class="typost-styled" data-font-id="37" style="font-family: var(--font-37)">Why</span> the';
		expect(countParagraphStyleConflicts(html, 0, 3)).toBe(1);
	});

	test('a span carrying only glyph-level attributes is not a conflict', () => {
		const html = '<span class="typost-styled" data-feature-settings="&quot;salt&quot; 2" style="font-feature-settings: &quot;salt&quot; 2">W</span>hy';
		expect(countParagraphStyleConflicts(html, 0, 3)).toBe(0);
	});

	test('a span outside the selection does not count', () => {
		expect(countParagraphStyleConflicts(FIRST_LETTER, 4, 28)).toBe(0);
	});
});

describe('stripParagraphStyleOverrides', () => {
	test('strips the first letter\'s font and features inside the styled wrapper', () => {
		const applied = '<span class="typost-styled" data-style-id="3">' + FIRST_LETTER + '</span>';
		const result = stripParagraphStyleOverrides(applied, 0, 28, 3);
		expect(result.stripped).toBe(1);
		// The inner span had nothing else — it is unwrapped entirely
		expect(result.content).toBe('<span class="typost-styled" data-style-id="3">Why the flourishes disappear</span>');
	});

	test('keeps a raw glyph alternate and fit-relative adjustments on the inner span', () => {
		const inner = '<span class="typost-styled" data-font-id="37" data-fitscale="0.6" data-feature-settings="&quot;salt&quot; 2" style="font-family: var(--font-37); font-feature-settings: &quot;salt&quot; 2; font-size: 0.6em">W</span>hy';
		const applied = '<span class="typost-styled" data-style-id="3">' + inner + '</span>';
		const result = stripParagraphStyleOverrides(applied, 0, 3, 3);
		expect(result.content).toBe('<span class="typost-styled" data-style-id="3"><span class="typost-styled" data-fitscale="0.6" data-feature-settings="&quot;salt&quot; 2" style="font-feature-settings: &quot;salt&quot; 2">W</span>hy</span>');
	});

	test('merge case: the styled span\'s own inline styling is removed, data-style-id stays', () => {
		const merged = '<span class="typost-styled" data-font-id="37" data-fontweight="700" data-style-id="3" style="font-family: var(--font-37); font-weight: 700">Why</span> the';
		const result = stripParagraphStyleOverrides(merged, 0, 3, 3);
		expect(result.content).toBe('<span class="typost-styled" data-style-id="3">Why</span> the');
	});

	test('a nested paragraph style inside the selection is replaced', () => {
		const applied = '<span class="typost-styled" data-style-id="3"><span class="typost-styled" data-style-id="5">Why</span> the</span>';
		const result = stripParagraphStyleOverrides(applied, 0, 7, 3);
		expect(result.content).toBe('<span class="typost-styled" data-style-id="3">Why the</span>');
	});

	test('spans carrying a different style id, or outside the range, are untouched', () => {
		const html = '<span class="typost-styled" data-style-id="9" data-font-id="4" style="font-family: var(--font-4)">Why</span> <span class="typost-styled" data-style-id="3">the</span>';
		const result = stripParagraphStyleOverrides(html, 4, 7, 3);
		expect(result.content).toBe(html);
		expect(result.stripped).toBe(0);
	});

	test('no-ops safely on empty input', () => {
		expect(stripParagraphStyleOverrides('', 0, 3, 3)).toEqual({ content: '', stripped: 0 });
		expect(stripParagraphStyleOverrides('abc', 0, 3, 0)).toEqual({ content: 'abc', stripped: 0 });
	});
});
