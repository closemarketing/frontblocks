const { registerBlockType } = wp.blocks;
const { Fragment, useState } = wp.element;
const { useSelect } = wp.data;
const {
	InspectorControls,
	useBlockProps,
	RichText,
	PanelColorSettings,
} = wp.blockEditor;
const {
	PanelBody,
	SelectControl,
	RangeControl,
	Button,
	__experimentalNumberControl: NumberControl,
	__experimentalToggleGroupControl: ToggleGroupControl,
	__experimentalToggleGroupControlOptionIcon: ToggleGroupControlOptionIcon,
} = wp.components;
const { __ } = wp.i18n;

const FONT_SIZE_UNITS = [
	{ label: 'px',  value: 'px'  },
	{ label: 'rem', value: 'rem' },
	{ label: 'em',  value: 'em'  },
	{ label: 'vw',  value: 'vw'  },
];

const TAG_OPTIONS = [
	{ label: 'h1', value: 'h1' },
	{ label: 'h2', value: 'h2' },
	{ label: 'h3', value: 'h3' },
	{ label: 'h4', value: 'h4' },
	{ label: 'h5', value: 'h5' },
	{ label: 'h6', value: 'h6' },
	{ label: 'p',  value: 'p'  },
	{ label: 'span (inline)', value: 'span' },
];

const FONT_WEIGHT_OPTIONS = [
	{ label: __( 'Thin (100)', 'frontblocks' ),       value: '100' },
	{ label: __( 'Extra Light (200)', 'frontblocks' ), value: '200' },
	{ label: __( 'Light (300)', 'frontblocks' ),       value: '300' },
	{ label: __( 'Normal (400)', 'frontblocks' ),      value: '400' },
	{ label: __( 'Medium (500)', 'frontblocks' ),      value: '500' },
	{ label: __( 'Semi Bold (600)', 'frontblocks' ),   value: '600' },
	{ label: __( 'Bold (700)', 'frontblocks' ),        value: '700' },
	{ label: __( 'Extra Bold (800)', 'frontblocks' ),  value: '800' },
	{ label: __( 'Black (900)', 'frontblocks' ),       value: '900' },
];

const ALIGN_LEFT_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M13 15H3v2h10v-2zm0-8H3v2h10V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/>
	</svg>
);
const ALIGN_CENTER_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/>
	</svg>
);
const ALIGN_RIGHT_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M3 21h18v-2H3v2zm8-4h10v-2H11v2zm-8-4h18v-2H3v2zm8-4h10V7H11v2zM3 3v2h18V3H3z"/>
	</svg>
);
const ALIGN_JUSTIFY_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M3 21h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18V7H3v2zm0-6v2h18V3H3z"/>
	</svg>
);

const TRANSFORM_NONE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="4" y="17" fontFamily="serif" fontSize="14" fontWeight="bold" fill="currentColor">Ag</text>
		<line x1="3" y1="21" x2="21" y2="3" stroke="currentColor" strokeWidth="1.5"/>
	</svg>
);
const TRANSFORM_UPPERCASE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="2" y="17" fontFamily="serif" fontSize="15" fontWeight="bold" fill="currentColor">AA</text>
	</svg>
);
const TRANSFORM_LOWERCASE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="2" y="17" fontFamily="serif" fontSize="15" fill="currentColor">aa</text>
	</svg>
);
const TRANSFORM_CAPITALIZE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="2" y="17" fontFamily="serif" fontSize="15" fill="currentColor">Aa</text>
	</svg>
);

const FONT_STYLE_OPTIONS = [
	{ label: __( 'Normal', 'frontblocks' ), value: 'normal' },
	{ label: __( 'Italic', 'frontblocks' ), value: 'italic' },
];

const ANIMATION_OPTIONS = [
	{ label: __( 'None', 'frontblocks' ),       value: 'none' },
	{ label: __( 'Fade In', 'frontblocks' ),    value: 'fade-in' },
	{ label: __( 'Typewriter', 'frontblocks' ),    value: 'typewriter' },
	{ label: __( 'Shuffle Text', 'frontblocks' ),  value: 'shuffle-text' },
	{ label: __( 'Slide Up', 'frontblocks' ),      value: 'slide-up' },
	{ label: __( 'Slide Down', 'frontblocks' ),    value: 'slide-down' },
	{ label: __( 'Slide Left', 'frontblocks' ),    value: 'slide-left' },
	{ label: __( 'Slide Right', 'frontblocks' ),   value: 'slide-right' },
	{ label: __( 'Drop In', 'frontblocks' ),       value: 'drop-in' },
	{ label: __( 'Swing', 'frontblocks' ),         value: 'swing' },
	{ label: __( 'Pulse', 'frontblocks' ),         value: 'pulse' },
	{ label: __( 'Flash', 'frontblocks' ),         value: 'flash' },
	{ label: __( 'Rubber Band', 'frontblocks' ),   value: 'rubber-band' },
	{ label: __( 'Wave', 'frontblocks' ),          value: 'wave' },
	{ label: __( 'Stretch', 'frontblocks' ),       value: 'stretch' },
	{ label: __( 'Squeeze', 'frontblocks' ),       value: 'squeeze' },
	{ label: __( 'Roll In', 'frontblocks' ),       value: 'roll-in' },
	{ label: __( 'Glitch', 'frontblocks' ),        value: 'glitch' },
	{ label: __( 'Random Reveal', 'frontblocks' ), value: 'random-reveal' },
	{ label: __( 'Flicker', 'frontblocks' ),       value: 'flicker' },
	{ label: __( 'Block Reveal', 'frontblocks' ),  value: 'block-reveal' },
	{ label: __( 'Scale In', 'frontblocks' ),      value: 'scale-in' },
	{ label: __( 'Blur In', 'frontblocks' ),       value: 'blur-in' },
	{ label: __( 'Glow In', 'frontblocks' ),       value: 'glow-in' },
	{ label: __( 'Bounce In', 'frontblocks' ),     value: 'bounce-in' },
	{ label: __( 'Flip In', 'frontblocks' ),       value: 'flip-in' },
	{ label: __( 'Rotate In', 'frontblocks' ),     value: 'rotate-in' },
];

function stripHtml( html ) {
	return html ? html.replace( /<[^>]*>/g, '' ) : '';
}

/* ── Animation preview registry ─────────────────────────────
   Each entry: { loop: bool, render( text, style, Tag, key ) → JSX }
   Add a new entry for every new animation type.
──────────────────────────────────────────────────────────── */
const ANIMATION_PREVIEWS = {
	'fade-in': {
		duration: ( text ) => ( text.length * 0.05 + 0.8 ) * 1000,
		render: function FadeInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.8;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblFadeIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'rotate-in': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function RotateInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblRotateIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'flip-in': {
		duration: ( text ) => ( text.length * 0.05 + 0.6 ) * 1000,
		render: function FlipInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.6;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblFlipIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'bounce-in': {
		duration: ( text ) => ( text.length * 0.08 + 0.6 ) * 1000,
		render: function BounceInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.6;
			const CHAR_DELAY    = 0.08;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblBounceIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'glow-in': {
		duration: ( text ) => ( text.length * 0.05 + 1 ) * 1000,
		render: function GlowInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 1;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblGlowIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'blur-in': {
		duration: ( text ) => ( text.length * 0.05 + 0.8 ) * 1000,
		render: function BlurInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.8;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblBlurIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'scale-in': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function ScaleInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblScaleIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'slide-up': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function SlideUpRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ { ...style, overflow: 'hidden' } } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblSlideUp ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'block-reveal': {
		duration: () => 1200,
		render: function BlockRevealRender( { text, style, Tag, animKey } ) {
			const { useEffect, useRef } = wp.element;
			const containerRef = useRef( null );

			useEffect( () => {
				const el = containerRef.current;
				if ( ! el ) return;

				el.style.position = 'relative';
				el.style.overflow = 'hidden';
				el.innerHTML      = '';

				const textSpan           = document.createElement( 'span' );
				textSpan.textContent     = text;
				textSpan.style.cssText   = 'display:inline-block;opacity:0;';

				const block           = document.createElement( 'span' );
				block.style.cssText   = 'position:absolute;top:0;left:0;width:100%;height:100%;background:currentColor;transform-origin:left;transform:scaleX(0);transition:transform 0.5s cubic-bezier(0.86,0,0.07,1);';

				el.appendChild( textSpan );
				el.appendChild( block );

				const t1 = setTimeout( () => {
					block.style.transform = 'scaleX(1)';
				}, 50 );

				const t2 = setTimeout( () => {
					textSpan.style.opacity      = '1';
					block.style.transformOrigin = 'right';
					block.style.transform       = 'scaleX(0)';
				}, 550 );

				return () => { clearTimeout( t1 ); clearTimeout( t2 ); };
			}, [ animKey, text ] );

			return <Tag ref={ containerRef } style={ style } key={ animKey } />;
		},
	},
	'flicker': {
		duration: ( text ) => ( text.length * 0.05 + 1.2 ) * 1000,
		render: function FlickerRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 1.2;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblFlicker ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'random-reveal': {
		duration: ( text ) => text.replace( / /g, '' ).length * 50,
		render: function RandomRevealRender( { text, style, Tag, animKey } ) {
			const { useEffect, useRef } = wp.element;
			const containerRef = useRef( null );

			useEffect( () => {
				const el = containerRef.current;
				if ( ! el ) return;
				el.innerHTML = '';

				const chars   = text.split( '' );
				const indices = chars.map( ( _, i ) => i ).sort( () => Math.random() - 0.5 );

				const spanList = chars.map( ( char ) => {
					const span           = document.createElement( 'span' );
					span.className       = 'frbl-char';
					span.textContent     = char;
					span.style.cssText   = 'display:inline-block;white-space:pre;opacity:0;';
					el.appendChild( span );
					return span;
				} );

				let i = 0;
				let timer;

				function reveal() {
					if ( i < indices.length ) {
						spanList[ indices[ i ] ].style.opacity = '1';
						i++;
						timer = setTimeout( reveal, 50 );
					}
				}

				reveal();
				return () => clearTimeout( timer );
			}, [ animKey, text ] );

			return <Tag ref={ containerRef } style={ style } key={ animKey } />;
		},
	},
	'glitch': {
		duration: ( text ) => ( text.length * 0.1 + 0.4 ) * 1000,
		render: function GlitchRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.4;
			const CHAR_DELAY    = 0.1;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblGlitch ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'roll-in': {
		duration: ( text ) => ( text.length * 0.05 + 0.6 ) * 1000,
		render: function RollInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.6;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ { ...style, overflow: 'hidden' } } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblRollIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'squeeze': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function SqueezeRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblSqueeze ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'stretch': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function StretchRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblStretch ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'wave': {
		duration: ( text ) => ( text.length * 0.08 + 0.6 ) * 1000,
		render: function WaveRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.6;
			const CHAR_DELAY    = 0.08;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblWave ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'rubber-band': {
		duration: ( text ) => ( text.length * 0.05 + 0.8 ) * 1000,
		render: function RubberBandRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.8;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblRubberBand ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'flash': {
		duration: ( text ) => ( text.length * 0.05 + 1 ) * 1000,
		render: function FlashRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 1;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblFlash ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'pulse': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function PulseRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblPulse ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'swing': {
		duration: ( text ) => ( text.length * 0.05 + 0.8 ) * 1000,
		render: function SwingRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.8;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ style } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							transformOrigin: 'top center',
							animation: `frblSwing ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'drop-in': {
		duration: ( text ) => ( text.length * 0.06 + 0.6 ) * 1000,
		render: function DropInRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.6;
			const CHAR_DELAY    = 0.06;
			return (
				<Tag style={ { ...style, overflow: 'hidden' } } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblDropIn ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'slide-right': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function SlideRightRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ { ...style, overflow: 'hidden' } } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblSlideRight ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'slide-left': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function SlideLeftRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ { ...style, overflow: 'hidden' } } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblSlideLeft ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'slide-down': {
		duration: ( text ) => ( text.length * 0.05 + 0.5 ) * 1000,
		render: function SlideDownRender( { text, style, Tag, animKey } ) {
			const CHAR_DURATION = 0.5;
			const CHAR_DELAY    = 0.05;
			return (
				<Tag style={ { ...style, overflow: 'hidden' } } key={ animKey }>
					{ text.split( '' ).map( ( char, i ) => (
						<span key={ i } style={ {
							display: 'inline-block',
							whiteSpace: 'pre',
							opacity: 0,
							animation: `frblSlideDown ${ CHAR_DURATION }s forwards`,
							animationDelay: `${ i * CHAR_DELAY }s`,
						} }>{ char }</span>
					) ) }
				</Tag>
			);
		},
	},
	'shuffle-text': {
		duration: ( text ) => ( text.replace( / /g, '' ).length * 2 + 15 ) * 30,
		render: function ShuffleTextRender( { text, style, Tag, animKey } ) {
			const { useEffect, useRef } = wp.element;
			const SYMBOLS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
			const containerRef = useRef( null );

			useEffect( () => {
				const el = containerRef.current;
				if ( ! el ) return;
				el.innerHTML = '';

				const chars    = text.split( '' );
				const spanList = chars.map( ( char ) => {
					const span       = document.createElement( 'span' );
					span.className   = 'frbl-char';
					el.appendChild( span );
					return { el: span, char, isSpace: char === ' ' };
				} );

				let frame  = 0;
				let rafId;
				let timerId;

				function update() {
					let allDone = true;
					spanList.forEach( ( item, i ) => {
						if ( item.isSpace ) { item.el.textContent = ' '; return; }
						const startFrame = i * 2;
						const endFrame   = startFrame + 15;
						if ( frame < startFrame ) {
							allDone = false; item.el.textContent = '';
						} else if ( frame < endFrame ) {
							allDone = false;
							item.el.textContent = SYMBOLS[ Math.floor( Math.random() * SYMBOLS.length ) ];
						} else {
							item.el.textContent = item.char;
						}
					} );
					frame++;
					if ( ! allDone ) {
						timerId = setTimeout( () => { rafId = requestAnimationFrame( update ); }, 30 );
					}
				}

				rafId = requestAnimationFrame( update );
				return () => { cancelAnimationFrame( rafId ); clearTimeout( timerId ); };
			}, [ animKey, text ] );

			return <Tag ref={ containerRef } style={ style } key={ animKey } />;
		},
	},
	'typewriter': {
		duration: ( text ) => text.length * 80,
		render: function TypewriterRender( { text, style, Tag, animKey } ) {
			const { useEffect, useRef } = wp.element;
			const containerRef = useRef( null );

			useEffect( () => {
				const el = containerRef.current;
				if ( ! el ) return;
				el.textContent = '';
				const chars = text.split( '' );
				let i = 0;
				let timer;
				function type() {
					if ( i < chars.length ) {
						el.textContent += chars[ i ];
						i++;
						timer = setTimeout( type, 80 );
					}
				}
				type();
				return () => clearTimeout( timer );
			}, [ animKey, text ] );

			return <Tag ref={ containerRef } style={ style } key={ animKey } />;
		},
	},
};

function AnimationPreview( { animationType, text, style, Tag } ) {
	const [ animKey, setAnimKey ] = useState( 0 );
	const entry = ANIMATION_PREVIEWS[ animationType ];

	useState( () => {
		if ( ! entry ) return;
		const ms = entry.duration( text );
		const t  = setTimeout( () => setAnimKey( ( k ) => k + 1 ), ms );
		return () => clearTimeout( t );
	} );

	if ( ! entry ) return null;
	return entry.render( { text, style, Tag, animKey } );
}

function TextAnimationEdit( props ) {
	const { attributes, setAttributes } = props;
	const [ isHovered,  setIsHovered  ] = useState( false );
	const [ isEditMode, setIsEditMode ] = useState( true );
	const {
		content,
		htmlTag,
		animationType,
		fontSize,
		fontSizeUnit,
		fontFamily,
		fontWeight,
		fontStyle,
		lineHeight,
		letterSpacing,
		textAlign,
		textTransform,
		textColorCustom,
		textColorHover,
		width,
		widthUnit,
		height,
		heightUnit,
	} = attributes;

	const fontFamilyOptions = useSelect( ( select ) => {
		try {
			const settings = select( 'core' ).getSettings();
			const families = settings.fontFamilies || {};
			const all = [
				...( families.theme   || [] ),
				...( families.custom  || [] ),
				...( families.default || [] ),
			];
			const options = [];
			all.forEach( ( f ) => {
				if ( f && f.name && f.fontFamily ) {
					options.push( { label: f.name, value: f.fontFamily } );
				}
			} );
			return options;
		} catch ( e ) {
			return [];
		}
	}, [] );

	const blockProps = useBlockProps( {
		className: 'frbl-text-animation',
		style: {
			fontSize:             fontSize ? `${ fontSize }${ fontSizeUnit || 'px' }` : undefined,
			fontFamily:           fontFamily || undefined,
			fontWeight:           fontWeight || undefined,
			fontStyle:            fontStyle !== 'normal' ? fontStyle : undefined,
			lineHeight:           lineHeight || undefined,
			letterSpacing:        letterSpacing ? `${ letterSpacing }em` : undefined,
			textAlign:            textAlign !== 'left' ? textAlign : undefined,
			textTransform:        textTransform !== 'none' ? textTransform : undefined,
			'--frbl-color':       textColorCustom || undefined,
			'--frbl-color-hover': textColorHover || undefined,
			color:                ( isHovered && textColorHover ) ? textColorHover : ( textColorCustom || undefined ),
			transition:           'color 0.3s ease',
			width:                width ? `${ width }${ widthUnit || 'px' }` : undefined,
			height:               height ? `${ height }${ heightUnit || 'px' }` : undefined,
			marginLeft:           0,
			marginRight:          'auto',
		},
		onMouseEnter: () => setIsHovered( true ),
		onMouseLeave: () => setIsHovered( false ),
	} );

	const hasAnimation = animationType && animationType !== 'none';
	const showPreview  = hasAnimation && ! isEditMode;

	const previewStyle = {
		fontSize:      blockProps.style.fontSize,
		fontFamily:    blockProps.style.fontFamily,
		fontWeight:    blockProps.style.fontWeight,
		fontStyle:     blockProps.style.fontStyle,
		lineHeight:    blockProps.style.lineHeight,
		letterSpacing: blockProps.style.letterSpacing,
		textAlign:     blockProps.style.textAlign,
		textTransform: blockProps.style.textTransform,
		color:         blockProps.style.color || blockProps.style['--frbl-color'] || undefined,
		width:         blockProps.style.width,
		height:        blockProps.style.height,
		marginLeft:    0,
		marginRight:   'auto',
	};

	return (
		<Fragment>
			<InspectorControls>

				<PanelBody title={ __( 'FrontBlocks Animation Text', 'frontblocks' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Animation Type', 'frontblocks' ) }
						value={ animationType }
						options={ ANIMATION_OPTIONS }
						onChange={ ( value ) => {
							setAttributes( { animationType: value } );
							if ( value && value !== 'none' ) {
								const entry = ANIMATION_PREVIEWS[ value ];
								const text  = stripHtml( content ) || __( 'Write your text here…', 'frontblocks' );
								const ms    = entry ? entry.duration( text ) : 2000;
								setIsEditMode( false );
								setTimeout( () => setIsEditMode( true ), ms );
							} else {
								setIsEditMode( true );
							}
						} }
					/>
					{ hasAnimation && (
						<Button
							variant="secondary"
							size="small"
							onClick={ () => {
								const entry = ANIMATION_PREVIEWS[ animationType ];
								const text  = stripHtml( content ) || __( 'Write your text here…', 'frontblocks' );
								const ms    = entry ? entry.duration( text ) : 2000;
								setIsEditMode( false );
								setTimeout( () => setIsEditMode( true ), ms );
							} }
							style={ { marginTop: '8px', width: '100%', justifyContent: 'center' } }
						>
							{ __( '▶ Preview animation', 'frontblocks' ) }
						</Button>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Typography', 'frontblocks' ) } initialOpen={ false }>

					<SelectControl
						label={ __( 'HTML Tag', 'frontblocks' ) }
						value={ htmlTag }
						options={ TAG_OPTIONS }
						onChange={ ( value ) => setAttributes( { htmlTag: value } ) }
					/>

					{ fontFamilyOptions.length > 0 && (
						<SelectControl
							label={ __( 'Font Family', 'frontblocks' ) }
							value={ fontFamily }
							options={ [ { label: __( 'Default', 'frontblocks' ), value: '' }, ...fontFamilyOptions ] }
							onChange={ ( value ) => setAttributes( { fontFamily: value } ) }
						/>
					) }

					<div style={ { marginBottom: '16px' } }>
						<p style={ { marginTop: 0, marginBottom: '8px', fontSize: '11px', fontWeight: '500', textTransform: 'uppercase', color: 'rgb(117, 117, 117)' } }>
							{ __( 'Font Size', 'frontblocks' ) }
						</p>
						<div style={ { display: 'flex', gap: '8px' } }>
							<div style={ { flex: 1, marginBottom: 0 } }>
								<NumberControl
									value={ fontSize || '' }
									onChange={ ( value ) => setAttributes( { fontSize: value ? parseFloat( value ) : undefined } ) }
									min={ 1 }
									step={ 1 }
									spinControls="native"
									hideLabelFromVision
									label={ __( 'Font Size', 'frontblocks' ) }
								/>
							</div>
							<div style={ { width: '80px', flexShrink: 0, marginBottom: 0 } }>
								<SelectControl
									value={ fontSizeUnit }
									options={ FONT_SIZE_UNITS }
									onChange={ ( value ) => setAttributes( { fontSizeUnit: value } ) }
									hideLabelFromVision
									label={ __( 'Unit', 'frontblocks' ) }
									__nextHasNoMarginBottom
								/>
							</div>
						</div>
					</div>

					<SelectControl
						label={ __( 'Font Weight', 'frontblocks' ) }
						value={ fontWeight }
						options={ [ { label: __( 'Default', 'frontblocks' ), value: '' }, ...FONT_WEIGHT_OPTIONS ] }
						onChange={ ( value ) => setAttributes( { fontWeight: value } ) }
					/>

					<SelectControl
						label={ __( 'Font Style', 'frontblocks' ) }
						value={ fontStyle }
						options={ FONT_STYLE_OPTIONS }
						onChange={ ( value ) => setAttributes( { fontStyle: value } ) }
					/>

					<ToggleGroupControl
						label={ __( 'Text Align', 'frontblocks' ) }
						value={ textAlign }
						onChange={ ( value ) => {
							const newAlign = value || 'left';
							const updates = { textAlign: newAlign };
							if ( newAlign !== 'left' ) {
								updates.width     = 100;
								updates.widthUnit = '%';
							}
							setAttributes( updates );
						} }
						isBlock
					>
						<ToggleGroupControlOptionIcon value="left"    icon={ ALIGN_LEFT_ICON }    label={ __( 'Left', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="center"  icon={ ALIGN_CENTER_ICON }  label={ __( 'Center', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="right"   icon={ ALIGN_RIGHT_ICON }   label={ __( 'Right', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="justify" icon={ ALIGN_JUSTIFY_ICON } label={ __( 'Justify', 'frontblocks' ) } />
					</ToggleGroupControl>

					<ToggleGroupControl
						label={ __( 'Text Transform', 'frontblocks' ) }
						value={ textTransform }
						onChange={ ( value ) => setAttributes( { textTransform: value || 'none' } ) }
						isBlock
					>
						<ToggleGroupControlOptionIcon value="none"       icon={ TRANSFORM_NONE_ICON }       label={ __( 'None', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="uppercase"  icon={ TRANSFORM_UPPERCASE_ICON }  label={ __( 'Uppercase', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="lowercase"  icon={ TRANSFORM_LOWERCASE_ICON }  label={ __( 'Lowercase', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="capitalize" icon={ TRANSFORM_CAPITALIZE_ICON } label={ __( 'Capitalize', 'frontblocks' ) } />
					</ToggleGroupControl>

					<RangeControl
						label={ __( 'Line Height', 'frontblocks' ) }
						value={ lineHeight }
						onChange={ ( value ) => setAttributes( { lineHeight: value } ) }
						min={ 0.5 }
						max={ 4 }
						step={ 0.05 }
						initialPosition={ 1.5 }
						allowReset
						resetFallbackValue={ undefined }
					/>

					<RangeControl
						label={ __( 'Letter Spacing (em)', 'frontblocks' ) }
						value={ letterSpacing }
						onChange={ ( value ) => setAttributes( { letterSpacing: value } ) }
						min={ -0.2 }
						max={ 1 }
						step={ 0.01 }
						initialPosition={ 0 }
						allowReset
						resetFallbackValue={ undefined }
					/>

				</PanelBody>

				<PanelBody title={ __( 'Dimensions', 'frontblocks' ) } initialOpen={ false }>

					<div style={ { marginBottom: '16px' } }>
						<p style={ { marginTop: 0, marginBottom: '8px', fontSize: '11px', fontWeight: '500', textTransform: 'uppercase', color: 'rgb(117, 117, 117)' } }>
							{ __( 'Width', 'frontblocks' ) }
						</p>
						<div style={ { display: 'flex', gap: '8px' } }>
							<div style={ { flex: 1 } }>
								<NumberControl
									value={ width || '' }
									onChange={ ( value ) => setAttributes( { width: value ? parseFloat( value ) : undefined } ) }
									min={ 0 }
									step={ 1 }
									spinControls="native"
									hideLabelFromVision
									label={ __( 'Width', 'frontblocks' ) }
									placeholder="auto"
								/>
							</div>
							<div style={ { width: '80px', flexShrink: 0 } }>
								<SelectControl
									value={ widthUnit }
									options={ [ { label: 'px', value: 'px' }, { label: '%', value: '%' }, { label: 'rem', value: 'rem' }, { label: 'em', value: 'em' }, { label: 'vw', value: 'vw' }, { label: 'ch', value: 'ch' } ] }
									onChange={ ( value ) => setAttributes( { widthUnit: value } ) }
									hideLabelFromVision
									label={ __( 'Unit', 'frontblocks' ) }
									__nextHasNoMarginBottom
								/>
							</div>
						</div>
					</div>

					<div style={ { marginBottom: '8px' } }>
						<p style={ { marginTop: 0, marginBottom: '8px', fontSize: '11px', fontWeight: '500', textTransform: 'uppercase', color: 'rgb(117, 117, 117)' } }>
							{ __( 'Height', 'frontblocks' ) }
						</p>
						<div style={ { display: 'flex', gap: '8px' } }>
							<div style={ { flex: 1 } }>
								<NumberControl
									value={ height || '' }
									onChange={ ( value ) => setAttributes( { height: value ? parseFloat( value ) : undefined } ) }
									min={ 0 }
									step={ 1 }
									spinControls="native"
									hideLabelFromVision
									label={ __( 'Height', 'frontblocks' ) }
									placeholder="auto"
								/>
							</div>
							<div style={ { width: '80px', flexShrink: 0 } }>
								<SelectControl
									value={ heightUnit }
									options={ [ { label: 'px', value: 'px' }, { label: '%', value: '%' }, { label: 'rem', value: 'rem' }, { label: 'em', value: 'em' }, { label: 'vh', value: 'vh' } ] }
									onChange={ ( value ) => setAttributes( { heightUnit: value } ) }
									hideLabelFromVision
									label={ __( 'Unit', 'frontblocks' ) }
									__nextHasNoMarginBottom
								/>
							</div>
						</div>
					</div>

				</PanelBody>

				<PanelColorSettings
					title={ __( 'Color', 'frontblocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							label: __( 'Text Color', 'frontblocks' ),
							value: textColorCustom,
							onChange: ( value ) => setAttributes( { textColorCustom: value || '' } ),
						},
						{
							label: __( 'Text Color Hover', 'frontblocks' ),
							value: textColorHover,
							onChange: ( value ) => setAttributes( { textColorHover: value || '' } ),
						},
					] }
				/>

			</InspectorControls>

			{ showPreview ? (
				<AnimationPreview
					animationType={ animationType }
					text={ stripHtml( content ) || __( 'Write your text here…', 'frontblocks' ) }
					style={ previewStyle }
					Tag={ htmlTag }
				/>
			) : (
				<RichText
					{ ...blockProps }
					tagName={ htmlTag }
					value={ content }
					onChange={ ( value ) => setAttributes( { content: value } ) }
					placeholder={ __( 'Write your text here…', 'frontblocks' ) }
					allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/underline', 'core/strikethrough', 'core/code' ] }
				/>
			) }
		</Fragment>
	);
}

registerBlockType( 'frontblocks/text-animation', {
	title:       __( 'Text Animation', 'frontblocks' ),
	description: __( 'Animated text block with typography controls. Add animation effects from the sidebar.', 'frontblocks' ),
	category:    'text',
	icon:        'editor-textcolor',
	keywords: [
		__( 'text', 'frontblocks' ),
		__( 'animation', 'frontblocks' ),
		__( 'heading', 'frontblocks' ),
		__( 'typography', 'frontblocks' ),
	],
	supports: {
		anchor: true,
		className: true,
		color: false,
		spacing: {
			margin:  true,
			padding: true,
		},
	},
	attributes: {
		content: {
			type:    'string',
			source:  'html',
			selector: '.frbl-text-animation',
			default: '',
		},
		htmlTag: {
			type:    'string',
			default: 'h2',
		},
		animationType: {
			type:    'string',
			default: 'none',
		},
		fontSize: {
			type:    'number',
			default: undefined,
		},
		fontSizeUnit: {
			type:    'string',
			default: 'px',
		},
		fontFamily: {
			type:    'string',
			default: '',
		},
		fontWeight: {
			type:    'string',
			default: '',
		},
		fontStyle: {
			type:    'string',
			default: 'normal',
		},
		lineHeight: {
			type:    'number',
			default: undefined,
		},
		letterSpacing: {
			type:    'number',
			default: undefined,
		},
		textAlign: {
			type:    'string',
			default: 'left',
		},
		textTransform: {
			type:    'string',
			default: 'none',
		},
		textColorCustom: {
			type:    'string',
			default: '',
		},
		textColorHover: {
			type:    'string',
			default: '',
		},
		width: {
			type:    'number',
			default: undefined,
		},
		widthUnit: {
			type:    'string',
			default: 'px',
		},
		height: {
			type:    'number',
			default: undefined,
		},
		heightUnit: {
			type:    'string',
			default: 'px',
		},
	},
	edit: TextAnimationEdit,
	save: function ( { attributes } ) {
		const {
			content,
			htmlTag: Tag,
			animationType,
			fontSize,
			fontSizeUnit,
			fontFamily,
			fontWeight,
			fontStyle,
			lineHeight,
			letterSpacing,
			textAlign,
			textTransform,
			textColorCustom,
			textColorHover,
			width,
			widthUnit,
			height,
			heightUnit,
		} = attributes;

		const style = {};
		if ( fontSize )                        style.fontSize             = `${ fontSize }${ fontSizeUnit || 'px' }`;
		if ( fontFamily )                      style.fontFamily           = fontFamily;
		if ( fontWeight )                      style.fontWeight           = fontWeight;
		if ( fontStyle && fontStyle !== 'normal' ) style.fontStyle        = fontStyle;
		if ( lineHeight )                      style.lineHeight           = lineHeight;
		if ( letterSpacing )                   style.letterSpacing        = `${ letterSpacing }em`;
		if ( textAlign && textAlign !== 'left' ) style.textAlign          = textAlign;
		if ( textTransform && textTransform !== 'none' ) style.textTransform = textTransform;
		if ( textColorCustom )                 style['--frbl-color']      = textColorCustom;
		if ( textColorHover )                  style['--frbl-color-hover']= textColorHover;
		if ( width )                           style.width                = `${ width }${ widthUnit || 'px' }`;
		if ( height )                          style.height               = `${ height }${ heightUnit || 'px' }`;

		const blockProps = wp.blockEditor.useBlockProps.save( {
			className: 'frbl-text-animation',
			style,
			'data-animation': ( animationType && animationType !== 'none' ) ? animationType : undefined,
		} );

		return (
			<Tag { ...blockProps }>
				<RichText.Content value={ content } />
			</Tag>
		);
	},
} );
