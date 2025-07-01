// Add custom animation controls to any block based on Animate.css
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { SelectControl, RangeControl, ToggleControl, PanelBody, Placeholder, Disabled } = wp.components;
const { __ } = wp.i18n;

// Organizar las animaciones por categorías
const animationOptions = [
    {
        label: __('None', 'frontblocks'),
        value: ''
    },
    {
        label: __('Attention Seekers', 'frontblocks'),
        options: [
            { label: 'bounce', value: 'bounce' },
            { label: 'flash', value: 'flash' },
            { label: 'pulse', value: 'pulse' },
            { label: 'rubberBand', value: 'rubberBand' },
            { label: 'shakeX', value: 'shakeX' },
            { label: 'shakeY', value: 'shakeY' },
            { label: 'headShake', value: 'headShake' },
            { label: 'swing', value: 'swing' },
            { label: 'tada', value: 'tada' },
            { label: 'wobble', value: 'wobble' },
            { label: 'jello', value: 'jello' },
            { label: 'heartBeat', value: 'heartBeat' }
        ]
    },
    {
        label: __('Back Entrances', 'frontblocks'),
        options: [
            { label: 'backInDown', value: 'backInDown' },
            { label: 'backInLeft', value: 'backInLeft' },
            { label: 'backInRight', value: 'backInRight' },
            { label: 'backInUp', value: 'backInUp' }
        ]
    },
    {
        label: __('Back Exits', 'frontblocks'),
        options: [
            { label: 'backOutDown', value: 'backOutDown' },
            { label: 'backOutLeft', value: 'backOutLeft' },
            { label: 'backOutRight', value: 'backOutRight' },
            { label: 'backOutUp', value: 'backOutUp' }
        ]
    },
    {
        label: __('Bouncing Entrances', 'frontblocks'),
        options: [
            { label: 'bounceIn', value: 'bounceIn' },
            { label: 'bounceInDown', value: 'bounceInDown' },
            { label: 'bounceInLeft', value: 'bounceInLeft' },
            { label: 'bounceInRight', value: 'bounceInRight' },
            { label: 'bounceInUp', value: 'bounceInUp' }
        ]
    },
    {
        label: __('Bouncing Exits', 'frontblocks'),
        options: [
            { label: 'bounceOut', value: 'bounceOut' },
            { label: 'bounceOutDown', value: 'bounceOutDown' },
            { label: 'bounceOutLeft', value: 'bounceOutLeft' },
            { label: 'bounceOutRight', value: 'bounceOutRight' },
            { label: 'bounceOutUp', value: 'bounceOutUp' }
        ]
    },
    {
        label: __('Fading Entrances', 'frontblocks'),
        options: [
            { label: 'fadeIn', value: 'fadeIn' },
            { label: 'fadeInDown', value: 'fadeInDown' },
            { label: 'fadeInDownBig', value: 'fadeInDownBig' },
            { label: 'fadeInLeft', value: 'fadeInLeft' },
            { label: 'fadeInLeftBig', value: 'fadeInLeftBig' },
            { label: 'fadeInRight', value: 'fadeInRight' },
            { label: 'fadeInRightBig', value: 'fadeInRightBig' },
            { label: 'fadeInUp', value: 'fadeInUp' },
            { label: 'fadeInUpBig', value: 'fadeInUpBig' },
            { label: 'fadeInTopLeft', value: 'fadeInTopLeft' },
            { label: 'fadeInTopRight', value: 'fadeInTopRight' },
            { label: 'fadeInBottomLeft', value: 'fadeInBottomLeft' },
            { label: 'fadeInBottomRight', value: 'fadeInBottomRight' }
        ]
    },
    {
        label: __('Fading Exits', 'frontblocks'),
        options: [
            { label: 'fadeOut', value: 'fadeOut' },
            { label: 'fadeOutDown', value: 'fadeOutDown' },
            { label: 'fadeOutDownBig', value: 'fadeOutDownBig' },
            { label: 'fadeOutLeft', value: 'fadeOutLeft' },
            { label: 'fadeOutLeftBig', value: 'fadeOutLeftBig' },
            { label: 'fadeOutRight', value: 'fadeOutRight' },
            { label: 'fadeOutRightBig', value: 'fadeOutRightBig' },
            { label: 'fadeOutUp', value: 'fadeOutUp' },
            { label: 'fadeOutUpBig', value: 'fadeOutUpBig' },
            { label: 'fadeOutTopLeft', value: 'fadeOutTopLeft' },
            { label: 'fadeOutTopRight', value: 'fadeOutTopRight' },
            { label: 'fadeOutBottomRight', value: 'fadeOutBottomRight' },
            { label: 'fadeOutBottomLeft', value: 'fadeOutBottomLeft' }
        ]
    },
    {
        label: __('Flippers', 'frontblocks'),
        options: [
            { label: 'flip', value: 'flip' },
            { label: 'flipInX', value: 'flipInX' },
            { label: 'flipInY', value: 'flipInY' },
            { label: 'flipOutX', value: 'flipOutX' },
            { label: 'flipOutY', value: 'flipOutY' }
        ]
    },
    {
        label: __('Lightspeed', 'frontblocks'),
        options: [
            { label: 'lightSpeedInRight', value: 'lightSpeedInRight' },
            { label: 'lightSpeedInLeft', value: 'lightSpeedInLeft' },
            { label: 'lightSpeedOutRight', value: 'lightSpeedOutRight' },
            { label: 'lightSpeedOutLeft', value: 'lightSpeedOutLeft' }
        ]
    },
    {
        label: __('Rotating Entrances', 'frontblocks'),
        options: [
            { label: 'rotateIn', value: 'rotateIn' },
            { label: 'rotateInDownLeft', value: 'rotateInDownLeft' },
            { label: 'rotateInDownRight', value: 'rotateInDownRight' },
            { label: 'rotateInUpLeft', value: 'rotateInUpLeft' },
            { label: 'rotateInUpRight', value: 'rotateInUpRight' }
        ]
    },
    {
        label: __('Rotating Exits', 'frontblocks'),
        options: [
            { label: 'rotateOut', value: 'rotateOut' },
            { label: 'rotateOutDownLeft', value: 'rotateOutDownLeft' },
            { label: 'rotateOutDownRight', value: 'rotateOutDownRight' },
            { label: 'rotateOutUpLeft', value: 'rotateOutUpLeft' },
            { label: 'rotateOutUpRight', value: 'rotateOutUpRight' }
        ]
    },
    {
        label: __('Specials', 'frontblocks'),
        options: [
            { label: 'hinge', value: 'hinge' },
            { label: 'jackInTheBox', value: 'jackInTheBox' },
            { label: 'rollIn', value: 'rollIn' },
            { label: 'rollOut', value: 'rollOut' }
        ]
    },
    {
        label: __('Zooming Entrances', 'frontblocks'),
        options: [
            { label: 'zoomIn', value: 'zoomIn' },
            { label: 'zoomInDown', value: 'zoomInDown' },
            { label: 'zoomInLeft', value: 'zoomInLeft' },
            { label: 'zoomInRight', value: 'zoomInRight' },
            { label: 'zoomInUp', value: 'zoomInUp' }
        ]
    },
    {
        label: __('Zooming Exits', 'frontblocks'),
        options: [
            { label: 'zoomOut', value: 'zoomOut' },
            { label: 'zoomOutDown', value: 'zoomOutDown' },
            { label: 'zoomOutLeft', value: 'zoomOutLeft' },
            { label: 'zoomOutRight', value: 'zoomOutRight' },
            { label: 'zoomOutUp', value: 'zoomOutUp' }
        ]
    },
    {
        label: __('Sliding Entrances', 'frontblocks'),
        options: [
            { label: 'slideInDown', value: 'slideInDown' },
            { label: 'slideInLeft', value: 'slideInLeft' },
            { label: 'slideInRight', value: 'slideInRight' },
            { label: 'slideInUp', value: 'slideInUp' }
        ]
    },
    {
        label: __('Sliding Exits', 'frontblocks'),
        options: [
            { label: 'slideOutDown', value: 'slideOutDown' },
            { label: 'slideOutLeft', value: 'slideOutLeft' },
            { label: 'slideOutRight', value: 'slideOutRight' },
            { label: 'slideOutUp', value: 'slideOutUp' }
        ]
    }
];

// Create flattened options with group labels for SelectControl
const createFlattenedOptions = () => {
    const flattenedOptions = [];
    
    animationOptions.forEach(category => {
        if (category.options) {
            // Add group header
            flattenedOptions.push({
                label: `━━━ ${category.label} ━━━`,
                value: '',
                disabled: true
            });
            // Add options under this group
            category.options.forEach(option => {
                flattenedOptions.push({
                    label: `  ${option.label}`,
                    value: option.value
                });
            });
        } else if (category.value !== undefined) {
            // Single option (like "None")
            flattenedOptions.push(category);
        }
    });
    
    return flattenedOptions;
};

function addAnimationControls(BlockEdit) {
    return (props) => {
        // Applicable to all blocks - no filtering by block name
        
        // Extract animation attributes with default values
        const {
            frblAnimation = '',
            frblAnimationDelay = 0,
            frblAnimationDuration = 1,
            frblAnimationRepeat = false,
            frblAnimationInfinite = false,
        } = props.attributes;
        
        // Create flattened options for the SelectControl
        const flattenedOptions = createFlattenedOptions();

        // Function to find the animation label from its value
        const getAnimationLabel = (value) => {
            const animation = flattenedOptions.find(option => option.value === value);
            return animation ? animation.label : value;
        };

        return (
            <Fragment>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody
                        title={__('Animations', 'frontblocks')}
                        initialOpen={false}
                    >
                        <SelectControl
                            label={__('Animation Type', 'frontblocks')}
                            value={frblAnimation}
                            options={flattenedOptions}
                            onChange={(value) => {
                                props.setAttributes({ frblAnimation: value });
                            }}
                        />

                        {frblAnimation && (
                            <>
                                <RangeControl
                                    label={__('Delay (seconds)', 'frontblocks')}
                                    value={frblAnimationDelay}
                                    onChange={(value) => props.setAttributes({ frblAnimationDelay: value })}
                                    min={0}
                                    max={10}
                                    step={0.1}
                                />
                                
                                <RangeControl
                                    label={__('Duration (seconds)', 'frontblocks')}
                                    value={frblAnimationDuration}
                                    onChange={(value) => props.setAttributes({ frblAnimationDuration: value })}
                                    min={0.1}
                                    max={10}
                                    step={0.1}
                                />
                                
                                <ToggleControl
                                    label={__('Repeat animation', 'frontblocks')}
                                    checked={frblAnimationRepeat}
                                    onChange={(value) => props.setAttributes({ frblAnimationRepeat: value })}
                                />
                                
                                {frblAnimationRepeat && (
                                    <ToggleControl
                                        label={__('Infinite repeat', 'frontblocks')}
                                        checked={frblAnimationInfinite}
                                        onChange={(value) => props.setAttributes({ frblAnimationInfinite: value })}
                                    />
                                )}
                            </>
                        )}
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}

// Add the animation controls to all blocks
addFilter(
    'editor.BlockEdit',
    'frontblocks/animation-controls',
    addAnimationControls
);

// Add custom class to blocks with animations
addFilter(
    'blocks.getSaveContent.extraProps',
    'frontblocks/apply-animations',
    (props, blockType, attributes) => {
        const { 
            frblAnimation,
            frblAnimationDelay,
            frblAnimationDuration,
            frblAnimationRepeat,
            frblAnimationInfinite
        } = attributes;

        if (frblAnimation) {
            // Add animate.css base class and the specific animation
            props.className = props.className ? 
                `${props.className} animate__animated animate__${frblAnimation}` : 
                `animate__animated animate__${frblAnimation}`;
            
            // Add style attribute if needed
            if (!props.style) {
                props.style = {};
            }
            
            // Set animation properties as inline styles
            if (frblAnimationDuration) {
                props.style['--animate-duration'] = `${frblAnimationDuration}s`;
            }
            
            if (frblAnimationDelay) {
                props.style['--animate-delay'] = `${frblAnimationDelay}s`;
            }
            
            if (frblAnimationInfinite) {
                props.style['--animate-repeat'] = 'infinite';
            } else if (frblAnimationRepeat) {
                props.style['--animate-repeat'] = '2';
            }
        }

        return props;
    }
);