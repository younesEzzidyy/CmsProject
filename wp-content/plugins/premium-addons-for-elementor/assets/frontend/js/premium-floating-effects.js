(function ($) {

    if ('undefined' == typeof window.paCheckSafari) {
        window.paCheckSafari = checkSafariBrowser();

        function checkSafariBrowser() {

            var iOS = /iP(hone|ad|od)/i.test(navigator.userAgent) && !window.MSStream;

            if (iOS) {
                var allowedBrowser = /(Chrome|CriOS|OPiOS|FxiOS)/.test(navigator.userAgent);

                if (!allowedBrowser) {
                    var isFireFox = '' === navigator.vendor;
                    allowedBrowser = allowedBrowser || isFireFox;
                }

                var isSafari = /WebKit/i.test(navigator.userAgent) && !allowedBrowser;

            } else {
                var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            }

            if (isSafari) {
                return true;
            }

            return false;
        }

    }

    $(window).on('elementor/frontend/init', function () {
        var paFloatingEffects = elementorModules.frontend.handlers.Base.extend({

            onInit: function () {

                elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

                if (this.$element.hasClass('premium-floating-effects-yes')) {

                    if (this.$element.hasClass('premium-disable-fe-yes')) {
                        if (window.paCheckSafari)
                            return;
                    }

                    this.run();
                }
            },

            run: function () {
                var _this = this,
                    eleSettings = this.getEffectSettings();

                // make sure that at least 1 setting exists
                var settingVals = Object.values(eleSettings.effectSettings);

                var safe = settingVals.findIndex(function (element) {
                    return (element !== undefined);
                });

                if (-1 === safe) {
                    return false;
                }

                // test this.
                // if (!this.settings) {
                //     return false;
                // }

                // unsing IntersectionObserverAPI.
                var eleObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            _this.applyEffects(eleSettings);
                            eleObserver.unobserve(entry.target); // to only excecute the callback func once.
                        }
                    });
                });

                eleObserver.observe(_this.$element[0]);

                // elementorFrontend.waypoint(
                //     _this.$element,
                //     function () {
                //         _this.applyEffects(eleSettings);
                //     }
                // );
            },

            getEffectSettings: function () {
                var settings = this.getElementSettings();

                var easing = 'steps' === settings.premium_fe_easing ? 'steps(' + settings.premium_fe_ease_step + ')' : settings.premium_fe_easing,
                    translateEnabled = 'yes' === settings.premium_fe_translate_switcher,
                    rotateEnabled = 'yes' === settings.premium_fe_rotate_switcher,
                    scaleEnabled = 'yes' === settings.premium_fe_scale_switcher,
                    skewEnabled = 'yes' === settings.premium_fe_skew_switcher,
                    opacityEnabled = 'yes' === settings.premium_fe_opacity_switcher,
                    bgColorEnabled = 'yes' === settings.premium_fe_bg_color_switcher,
                    blurEnabled = 'yes' === settings.premium_fe_blur_switcher,
                    contrastEnabled = 'yes' === settings.premium_fe_contrast_switcher,
                    gScaleEnabled = 'yes' === settings.premium_fe_gScale_switcher,
                    hueEnabled = 'yes' === settings.premium_fe_hue_switcher,
                    brightEnabled = 'yes' === settings.premium_fe_brightness_switcher,
                    satEnabled = 'yes' === settings.premium_fe_saturate_switcher,
                    generalSettings = {
                        direction: settings.premium_fe_direction,
                        loop: 'default' === settings.premium_fe_loop ? true : settings.premium_fe_loop_number,
                        easing: easing,
                        target: '' !== settings.premium_fe_target ? settings.premium_fe_target : '',
                    };

                var eleSettings = {
                    general: generalSettings,
                    effectSettings: {}
                };

                if (translateEnabled) {
                    eleSettings.effectSettings.translate = {
                        'x_param_from': settings.premium_fe_Xtranslate.sizes.from,
                        'x_param_to': settings.premium_fe_Xtranslate.sizes.to,
                        'y_param_from': settings.premium_fe_Ytranslate.sizes.from,
                        'y_param_to': settings.premium_fe_Ytranslate.sizes.to,
                        'duration': settings.premium_fe_trans_duration.size,
                        'delay': settings.premium_fe_trans_delay.size,
                    }
                }

                if (rotateEnabled) {
                    eleSettings.effectSettings.rotate = {
                        'x_param_from': settings.premium_fe_Xrotate.sizes.from,
                        'x_param_to': settings.premium_fe_Xrotate.sizes.to,
                        'y_param_from': settings.premium_fe_Yrotate.sizes.from,
                        'y_param_to': settings.premium_fe_Yrotate.sizes.to,
                        'z_param_from': settings.premium_fe_Zrotate.sizes.from,
                        'z_param_to': settings.premium_fe_Zrotate.sizes.to,
                        'duration': settings.premium_fe_rotate_duration.size,
                        'delay': settings.premium_fe_rotate_delay.size,
                    }
                }

                if (scaleEnabled) {
                    eleSettings.effectSettings.scale = {
                        'x_param_from': settings.premium_fe_Xscale.sizes.from,
                        'x_param_to': settings.premium_fe_Xscale.sizes.to,
                        'y_param_from': settings.premium_fe_Yscale.sizes.from,
                        'y_param_to': settings.premium_fe_Yscale.sizes.to,
                        'duration': settings.premium_fe_scale_duration.size,
                        'delay': settings.premium_fe_scale_delay.size,
                    }
                }

                if (skewEnabled) {
                    eleSettings.effectSettings.skew = {
                        'x_param_from': settings.premium_fe_Xskew.sizes.from,
                        'x_param_to': settings.premium_fe_Xskew.sizes.to,
                        'y_param_from': settings.premium_fe_Yskew.sizes.from,
                        'y_param_to': settings.premium_fe_Yskew.sizes.to,
                        'duration': settings.premium_fe_skew_duration.size,
                        'delay': settings.premium_fe_skew_delay.size,
                    }
                }

                if (PremiumFESettings.papro_installed) {
                    if (opacityEnabled) {
                        eleSettings.effectSettings.opacity = {
                            'from': settings.premium_fe_opacity.sizes.from / 100,
                            'to': settings.premium_fe_opacity.sizes.to / 100,
                            'duration': settings.premium_fe_opacity_duration.size,
                            'delay': settings.premium_fe_opacity_delay.size
                        };
                    }

                    if (bgColorEnabled) {
                        eleSettings.effectSettings.bgColor = {
                            'from': settings.premium_fe_bg_color_from,
                            'to': settings.premium_fe_bg_color_to,
                            'duration': settings.premium_fe_bg_color_duration.size,
                            'delay': settings.premium_fe_bg_color_delay.size,
                        }
                    }

                    if (blurEnabled) {
                        eleSettings.effectSettings.blur = {
                            'from': 'blur(' + settings.premium_fe_blur_val.sizes.from + 'px)',
                            'to': 'blur(' + settings.premium_fe_blur_val.sizes.to + 'px)',
                            'duration': settings.premium_fe_blur_duration.size,
                            'delay': settings.premium_fe_blur_delay.size,
                        }
                    }

                    if (contrastEnabled) {
                        eleSettings.effectSettings.contrast = {
                            'from': 'contrast(' + settings.premium_fe_contrast_val.sizes.from + '%)',
                            'to': 'contrast(' + settings.premium_fe_contrast_val.sizes.to + '%)',
                            'duration': settings.premium_fe_contrast_duration.size,
                            'delay': settings.premium_fe_contrast_delay.size,
                        }
                    }

                    if (gScaleEnabled) {
                        eleSettings.effectSettings.gScale = {
                            'from': 'grayscale(' + settings.premium_fe_gScale_val.sizes.from + '%)',
                            'to': 'grayscale(' + settings.premium_fe_gScale_val.sizes.to + '%)',
                            'duration': settings.premium_fe_gScale_duration.size,
                            'delay': settings.premium_fe_gScale_delay.size,
                        }
                    }

                    if (hueEnabled) {
                        eleSettings.effectSettings.hue = {
                            'from': 'hue-rotate(' + settings.premium_fe_hue_val.sizes.from + 'deg)',
                            'to': 'hue-rotate(' + settings.premium_fe_hue_val.sizes.to + 'deg)',
                            'duration': settings.premium_fe_hue_duration.size,
                            'delay': settings.premium_fe_hue_delay.size,
                        }
                    }

                    if (brightEnabled) {
                        eleSettings.effectSettings.bright = {
                            'from': 'brightness(' + settings.premium_fe_brightness_val.sizes.from + '%)',
                            'to': 'brightness(' + settings.premium_fe_brightness_val.sizes.to + '%)',
                            'duration': settings.premium_fe_brightness_duration.size,
                            'delay': settings.premium_fe_brightness_delay.size,
                        }
                    }

                    if (satEnabled) {
                        eleSettings.effectSettings.sat = {
                            'from': 'saturate(' + settings.premium_fe_saturate_val.sizes.from + '%)',
                            'to': 'saturate(' + settings.premium_fe_saturate_val.sizes.to + '%)',
                            'duration': settings.premium_fe_saturate_duration.size,
                            'delay': settings.premium_fe_saturate_delay.size,
                        }
                    }

                }

                return eleSettings;
            },

            applyEffects: function (eleSettings) {
                var settings = eleSettings,
                    effectSettings = settings.effectSettings,
                    $widgetContainer = this.$element.find('.elementor-widget-container')[0],
                    filterArr = [];

                if (settings.general.target) {
                    var targetSelector = settings.general.target;

                    // If the selector does not exists in the current widget, then search in the whole page.
                    $widgetContainer = this.$element.find(targetSelector).length > 0 ? '.elementor-element-' + this.$element.data('id') + ' ' + targetSelector : targetSelector;
                }

                var animeSettings = {
                    targets: $widgetContainer,
                    loop: settings.general.loop,
                    direction: settings.general.direction,
                    easing: settings.general.easing,
                };

                if (effectSettings.translate) {
                    var translate = effectSettings.translate,
                        x_translate = {
                            value: [translate.x_param_from || 0, translate.x_param_to || 0],
                            duration: translate.duration,
                            delay: translate.delay || 0
                        },
                        y_translate = {
                            value: [translate.y_param_from || 0, translate.y_param_to || 0],
                            duration: translate.duration,
                            delay: translate.delay || 0,
                        };

                    animeSettings.translateX = x_translate;
                    animeSettings.translateY = y_translate;
                }

                if (effectSettings.rotate) {
                    var rotate = effectSettings.rotate,
                        x_rotate = {
                            duration: rotate.duration,
                            delay: rotate.delay || 0,
                            value: [rotate.x_param_from || 0, rotate.x_param_to || 0],
                        },
                        y_rotate = {
                            duration: rotate.duration,
                            delay: rotate.delay || 0,
                            value: [rotate.y_param_from || 0, rotate.y_param_to || 0],
                        },
                        z_rotate = {
                            duration: rotate.duration,
                            delay: rotate.delay || 0,
                            value: [rotate.z_param_from || 0, rotate.z_param_to || 0],
                        };

                    animeSettings.rotateX = x_rotate;
                    animeSettings.rotateY = y_rotate;
                    animeSettings.rotateZ = z_rotate;
                }

                if (effectSettings.scale) {
                    var scale = effectSettings.scale,
                        x_scale = {
                            value: [scale.x_param_from || 0, scale.x_param_to || 0],
                            duration: scale.duration,
                            delay: scale.delay || 0
                        },
                        y_scale = {
                            value: [scale.y_param_from || 0, scale.y_param_to || 0],
                            duration: scale.duration,
                            delay: scale.delay || 0,
                        };

                    animeSettings.scaleX = x_scale;
                    animeSettings.scaleY = y_scale;
                }

                if (effectSettings.skew) {
                    var skew = effectSettings.skew,
                        x_skew = {
                            value: [skew.x_param_from || 0, skew.x_param_to || 0],
                            duration: skew.duration,
                            delay: skew.delay || 0
                        },
                        y_skew = {
                            value: [skew.y_param_from || 0, skew.y_param_to || 0],
                            duration: skew.duration,
                            delay: skew.delay || 0,
                        };

                    animeSettings.skewX = x_skew;
                    animeSettings.skewY = y_skew;
                }

                if (effectSettings.opacity) {
                    var opacity = effectSettings.opacity;

                    animeSettings.opacity = {
                        value: [opacity.from || 0, opacity.to || 0],
                        duration: opacity.duration,
                        delay: opacity.delay || 0
                    };
                }

                if (effectSettings.bgColor) {
                    var bgColor = effectSettings.bgColor;

                    animeSettings.backgroundColor = {
                        value: [bgColor.from || 0, bgColor.to || 0],
                        duration: bgColor.duration,
                        delay: bgColor.delay || 0
                    };
                }

                if (effectSettings.blur) {
                    var blur = effectSettings.blur,
                        blurEffect = {
                            value: [blur.from || 0, blur.to || 0],
                            duration: blur.duration,
                            delay: blur.delay || 0
                        };

                    filterArr.push(blurEffect);
                }

                if (effectSettings.hue) {
                    var hue = effectSettings.hue,
                        hueEffect = {
                            value: [hue.from || 0, hue.to || 0],
                            duration: hue.duration,
                            delay: hue.delay || 0
                        };

                    filterArr.push(hueEffect);
                }

                if (effectSettings.gScale) {
                    var gScale = effectSettings.gScale,
                        gScaleEffect = {
                            value: [gScale.from || 0, gScale.to || 0],
                            duration: gScale.duration,
                            delay: gScale.delay || 0
                        };

                    filterArr.push(gScaleEffect);
                }

                if (effectSettings.contrast) {
                    var contrast = effectSettings.contrast,
                        contrastEffect = {
                            value: [contrast.from || 0, contrast.to || 0],
                            duration: contrast.duration,
                            delay: contrast.delay || 0
                        };

                    filterArr.push(contrastEffect);
                }

                if (effectSettings.bright) {
                    var bright = effectSettings.bright,
                        brightEffect = {
                            value: [bright.from || 0, bright.to || 0],
                            duration: bright.duration,
                            delay: bright.delay || 0
                        };

                    filterArr.push(brightEffect);
                }

                if (effectSettings.sat) {
                    var sat = effectSettings.sat,
                        satEffect = {
                            value: [sat.from || 0, sat.to || 0],
                            duration: sat.duration,
                            delay: sat.delay || 0
                        };

                    filterArr.push(satEffect);
                }

                //add filter settings to animation settings
                if (filterArr.length !== 0) {
                    animeSettings.filter = filterArr;
                }

                anime(animeSettings);
            }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(paFloatingEffects, {
                $element: $scope
            });
        });

    });

})(jQuery);