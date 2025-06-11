(function( $ ) {
    'use strict';

    // Helper function to generate CSS
    function generatePositionCss(position, offsetX, offsetY) {
        console.log('JS: Generating CSS for', position, offsetX, offsetY);
        var css = {};
        // Basic validation for offsets, ensuring they are valid CSS values
        var validOffsetPattern = /^-?\d+(px|%|em|rem|vw|vh|auto)$/;
        offsetX = validOffsetPattern.test(offsetX) ? offsetX : '0px';
        offsetY = validOffsetPattern.test(offsetY) ? offsetY : '0px';

        switch (position) {
            case 'top-left': css = { top: offsetY, left: offsetX, right: 'auto', bottom: 'auto', transform: 'none' }; break;
            case 'top-center': css = { top: offsetY, left: 'calc(50% + ' + offsetX + ')', right: 'auto', bottom: 'auto', transform: 'translateX(-50%)' }; break;
            case 'top-right': css = { top: offsetY, right: offsetX, left: 'auto', bottom: 'auto', transform: 'none' }; break;
            case 'center-left': css = { top: 'calc(50% + ' + offsetY + ')', left: offsetX, right: 'auto', bottom: 'auto', transform: 'translateY(-50%)' }; break;
            case 'center-center': css = { top: 'calc(50% + ' + offsetY + ')', left: 'calc(50% + ' + offsetX + ')', right: 'auto', bottom: 'auto', transform: 'translate(-50%, -50%)' }; break;
            case 'center-right': css = { top: 'calc(50% + ' + offsetY + ')', right: offsetX, left: 'auto', bottom: 'auto', transform: 'translateY(-50%)' }; break;
            case 'bottom-left': css = { bottom: offsetY, left: offsetX, right: 'auto', top: 'auto', transform: 'none' }; break;
            case 'bottom-center': css = { bottom: offsetY, left: 'calc(50% + ' + offsetX + ')', right: 'auto', top: 'auto', transform: 'translateX(-50%)' }; break;
            case 'bottom-right': css = { bottom: offsetY, right: offsetX, left: 'auto', top: 'auto', transform: 'none' }; break;
            default: css = { top: '20px', left: 'calc(50% + 0px)', transform: 'translateX(-50%)' }; break; // Fallback
        }
        return css;
    }

    // Object to hold current Customizer values, initialized with defaults (though wp.customize.bind will update them)
    var currentValues = {
        desktop_position: 'top-center', desktop_offset_x: '0px', desktop_offset_y: '20px',
        mobile_position: 'top-center', mobile_offset_x: '0px', mobile_offset_y: '10px'
    };

    // Function to apply styles
    function applyStyles() {
        var desktopCss = generatePositionCss(currentValues.desktop_position, currentValues.desktop_offset_x, currentValues.desktop_offset_y);
        // Ensure the container is targeted correctly.
        // The PHP script uses #flek90-floating-container
        $('#flek90-floating-container').css(desktopCss);

        var mobileCssRules = '';
        var mobileCss = generatePositionCss(currentValues.mobile_position, currentValues.mobile_offset_x, currentValues.mobile_offset_y);
        for (var rule in mobileCss) {
            if (mobileCss.hasOwnProperty(rule)) {
                // Constructing rule: property: value !important;
                mobileCssRules += rule.replace(/([A-Z])/g, '-$1').toLowerCase() + ': ' + mobileCss[rule] + ' !important; ';
            }
        }

        var styleTagId = 'flek90-mobile-customizer-styles';
        var $styleTag = $('#' + styleTagId);
        if (!$styleTag.length) {
            $styleTag = $('<style type="text/css" id="' + styleTagId + '"></style>').appendTo('head');
        }
        // Ensure the media query and selector are correct.
        $styleTag.text('@media (max-width: 768px) { #flek90-floating-container { ' + mobileCssRules + '} }');
        console.log('JS: Applied styles. Desktop:', desktopCss, 'Mobile CSS Rules:', mobileCssRules);
    }

    // Listen for changes to settings
    var settingsToWatch = [
        'desktop_position', 'desktop_offset_x', 'desktop_offset_y',
        'mobile_position', 'mobile_offset_x', 'mobile_offset_y'
    ];

    wp.customize.bind('ready', function() {
        settingsToWatch.forEach(function(settingKey) {
            wp.customize('flek90_ff_customizer_settings[' + settingKey + ']', function(value) {
                // Store initial value
                currentValues[settingKey] = value.get();
                // Bind to changes
                value.bind(function(newval) {
                    console.log('JS: Setting changed - ' + settingKey + ' to ' + newval);
                    currentValues[settingKey] = newval;
                    applyStyles();
                });
            });
        });
        // Initial application of styles once Customizer is ready and settings are bound
        applyStyles();
    });

})( jQuery );
