( function () {
	'use strict';

	// Groups .frbl-char spans into word-level wrappers so browser line-wrapping
	// respects word boundaries instead of breaking mid-word.
	// Each animation file calls this after creating its spans.
	window.FrblWordWrap = function ( el ) {
		var chars = Array.from( el.querySelectorAll( ':scope > .frbl-char' ) );
		if ( ! chars.length ) return;

		var frag   = document.createDocumentFragment();
		var wordEl = null;

		chars.forEach( function ( ch ) {
			// Use data-char attribute when textContent is not yet set (e.g. shuffle-text).
			var txt     = ch.dataset.char !== undefined ? ch.dataset.char : ch.textContent;
			var isSpace = txt === ' ' || txt === ' ';
			if ( isSpace ) {
				wordEl = null;
				frag.appendChild( ch );
			} else {
				if ( ! wordEl ) {
					wordEl = document.createElement( 'span' );
					wordEl.style.cssText = 'display:inline-block;white-space:nowrap;';
					frag.appendChild( wordEl );
				}
				wordEl.appendChild( ch );
			}
		} );

		el.appendChild( frag );
	};

	// Every animation handler rebuilds its element's innerHTML from plain
	// text (el.textContent), one <span> per character. That silently
	// discards any inline formatting in the source content — most
	// importantly, a link the author added to part of the text: the <a>
	// itself is destroyed and only its unlinked text survives.
	//
	// Rather than teach all ~33 handlers to reconstruct arbitrary inline
	// markup around character spans, skip the destructive rebuild
	// entirely for any element whose content contains inline elements:
	// the text is left exactly as authored (links stay clickable) and
	// simply isn't character-animated. A missing animation on a link-
	// or bold-containing line is a much safer failure than silently
	// destroying the author's link.
	var INLINE_FORMAT_TAGS = { A: 1, STRONG: 1, B: 1, EM: 1, I: 1, MARK: 1, CODE: 1, ABBR: 1, SUB: 1, SUP: 1, SPAN: 1, KBD: 1 };

	function hasInlineFormatting( el ) {
		var children = el.children || [];
		for ( var i = 0; i < children.length; i++ ) {
			if ( INLINE_FORMAT_TAGS[ children[ i ].tagName ] ) {
				return true;
			}
		}
		return false;
	}

	window.FrblHasInlineFormatting = hasInlineFormatting;

	function init() {
		document.querySelectorAll( '.frbl-text-animation[data-animation]' ).forEach( function ( el ) {
			if ( hasInlineFormatting( el ) ) {
				return;
			}

			var type    = el.getAttribute( 'data-animation' );
			var handler = window.FrblAnimations && window.FrblAnimations[ type ];
			if ( typeof handler === 'function' ) {
				el._frblLoop = el.getAttribute( 'data-animation-loop' ) === '1';
				handler( el );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
