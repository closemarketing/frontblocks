const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, TextControl } = wp.components;
const { select, dispatch } = wp.data;
const { __ } = wp.i18n;

const COUNTER_BLOCKS = [
	'generateblocks/text',
	'generateblocks/headline',
	'core/heading',
	'core/paragraph',
];

wp.hooks.addFilter(
	'blocks.registerBlockType',
	'frontblocks/add-counter-attribute',
	( settings, name ) => {
		if ( ! COUNTER_BLOCKS.includes( name ) ) {
			return settings;
		}
		settings.attributes = Object.assign( settings.attributes, {
			isCounterActive: {
				type: 'boolean',
				default: false,
			},
			animationDuration: {
				type: 'number',
				default: 2000,
			},
			finalNumber: {
				type: 'string',
				default: '',
			},
			numberPrefix: {
				type: 'string',
				default: '',
			},
			numberSuffix: {
				type: 'string',
				default: '',
			},
		} );
		return settings;
	}
);

const withCounterControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( ! COUNTER_BLOCKS.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes, clientId } = props;
		const { isCounterActive, animationDuration, finalNumber, numberPrefix, numberSuffix } = attributes;

		const durationInSeconds = animationDuration / 1000;

		useEffect( () => {
			if ( isCounterActive && finalNumber ) {
				const block = select( 'core/block-editor' ).getBlock( clientId );
				if ( ! block ) return;

				const formattedNumber = `${ numberPrefix }${ finalNumber }${ numberSuffix }`;

				if ( block.attributes.content !== formattedNumber ) {
					dispatch( 'core/block-editor' ).updateBlockAttributes( clientId, {
						content: formattedNumber,
					} );
				}
			}
		}, [ isCounterActive, finalNumber, numberPrefix, numberSuffix, clientId ] );

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'FrontBlocks - Counter Effect', 'frontblocks' ) }
						initialOpen={ false }
					>
						<ToggleControl
							label={ __( 'Activate Counter Effect', 'frontblocks' ) }
							help={ isCounterActive ?
								__( 'The number will animate on scroll.', 'frontblocks' ) :
								__( 'Enable to activate counting animation.', 'frontblocks' )
							}
							checked={ isCounterActive }
							onChange={ ( val ) => setAttributes( { isCounterActive: val } ) }
						/>

						{ isCounterActive && (
							<>
								<TextControl
									label={ __( 'Final Number', 'frontblocks' ) }
									value={ finalNumber }
									onChange={ ( val ) => setAttributes( { finalNumber: val } ) }
									help={ __( 'Number to count up to (e.g.: 100).', 'frontblocks' ) }
								/>

								<TextControl
									label={ __( 'Number Prefix', 'frontblocks' ) }
									value={ numberPrefix }
									onChange={ ( val ) => setAttributes( { numberPrefix: val } ) }
									help={ __( 'Text before the number (e.g.: $, €).', 'frontblocks' ) }
								/>

								<TextControl
									label={ __( 'Number Suffix', 'frontblocks' ) }
									value={ numberSuffix }
									onChange={ ( val ) => setAttributes( { numberSuffix: val } ) }
									help={ __( 'Text after the number (e.g.: %, +).', 'frontblocks' ) }
								/>

								<TextControl
									label={ __( 'Animation Duration (seconds)', 'frontblocks' ) }
									value={ durationInSeconds }
									onChange={ ( val ) => {
										const seconds      = parseFloat( val );
										const milliseconds = isNaN( seconds ) ? 0 : seconds * 1000;
										setAttributes( { animationDuration: milliseconds } );
									} }
									type="number"
									step="0.1"
									help={ __( 'Duration of the animation.', 'frontblocks' ) }
								/>
							</>
						) }

						<p style={ { marginTop: '10px', fontSize: '12px', color: '#777' } }>
							<small>{ __( "Text must begin with a number (e.g., '123', '+500', '€100').", 'frontblocks' ) }</small>
						</p>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withCounterControl' );

wp.hooks.addFilter(
	'editor.BlockEdit',
	'frontblocks/counter-control',
	withCounterControl
);
