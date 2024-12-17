/*
*jQuery browser plugin detection 1.0.2
* http://plugins.jquery.com/project/jqplugin
* Checks for plugins / mimetypes supported in the browser extending the jQuery.browser object
* Copyright (c) 2008 Leonardo Rossetti motw.leo@gmail.com
* MIT License: http://www.opensource.org/licenses/mit-license.php
  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
*/

(function ($) {

    var a = !1;
    window.JQClass = function () { }, JQClass.classes = {}, JQClass.extend = function t(e) {
        var s = this.prototype;
        a = !0;
        var i = new this;
        for (var n in a = !1, e) i[n] = "function" == typeof e[n] && "function" == typeof s[n] ? function (i, n) {
            return function () {
                var t = this._super;
                this._super = function (t) {
                    return s[i].apply(this, t)
                };
                var e = n.apply(this, arguments);
                return this._super = t, e
            }
        }(n, e[n]) : e[n];

        function o() {
            !a && this._init && this._init.apply(this, arguments)
        }
        return ((o.prototype = i).constructor = o).extend = t, o
    }

    function camelCase(t) {
        return t.replace(/-([a-z])/g, function (t, e) {
            return e.toUpperCase()
        })
    }
    JQClass.classes.JQPlugin = JQClass.extend({
        name: "plugin",
        defaultOptions: {},
        regionalOptions: {},
        _getters: [],
        _getMarker: function () {
            return "is-" + this.name
        },
        _init: function () {

            $.extend(this.defaultOptions, this.regionalOptions && this.regionalOptions[""] || {});
            var i = camelCase(this.name);

            $[i] = this, $.fn[i] = function (t) {

                var e = Array.prototype.slice.call(arguments, 1);

                return $[i]._isNotChained(t, e) ? $[i][t].apply($[i], [this[0]].concat(e)) : this.each(function () {

                    if ("string" == typeof t) {
                        if ("_" === t[0] || !$[i][t]) throw "Unknown method: " + t;
                        $[i][t].apply($[i], [this].concat(e))
                    } else {
                        $[i]._attach(this, t)
                    }

                })
            }
        },
        setDefaults: function (t) {
            $.extend(this.defaultOptions, t || {})
        },
        _isNotChained: function (t, e) {
            return "option" === t && (0 === e.length || 1 === e.length && "string" == typeof e[0]) || -1 < $.inArray(t, this._getters)
        },
        _attach: function (t, e) {
            if (!(t = $(t)).hasClass(this._getMarker())) {
                t.addClass(this._getMarker()), e = $.extend({}, this.defaultOptions, this._getMetadata(t), e || {});
                var i = $.extend({
                    name: this.name,
                    elem: t,
                    options: e
                }, this._instSettings(t, e));
                t.data(this.name, i), this._postAttach(t, i), this.option(t, e)
            }
        },
        _instSettings: function (t, e) {
            return {}
        },
        _postAttach: function (t, e) { },
        _getMetadata: function (d) {
            try {
                var f = d.data(this.name.toLowerCase()) || "";
                for (var g in f = f.replace(/'/g, '"'), f = f.replace(/([a-zA-Z0-9]+):/g, function (t, e, i) {
                    var n = f.substring(0, i).match(/"/g);
                    return n && n.length % 2 != 0 ? e + ":" : '"' + e + '":'
                }), f = $.parseJSON("{" + f + "}"), f) {
                    var h = f[g];
                    "string" == typeof h && h.match(/^new Date\((.*)\)$/) && (f[g] = eval(h))
                }
                return f
            } catch (t) {
                return {}
            }
        },
        _getInst: function (t) {
            return $(t).data(this.name) || {}
        },
        option: function (t, e, i) {
            var n = (t = $(t)).data(this.name);
            if (!e || "string" == typeof e && null == i) return (s = (n || {}).options) && e ? s[e] : s;
            if (t.hasClass(this._getMarker())) {
                var s = e || {};
                "string" == typeof e && ((s = {})[e] = i), this._optionsChanged(t, n, s), $.extend(n.options, s)
            }
        },
        _optionsChanged: function (t, e, i) { },
        destroy: function (t) {
            (t = $(t)).hasClass(this._getMarker()) && (this._preDestroy(t, this._getInst(t)), t.removeData(this.name).removeClass(this._getMarker()))
        },
        _preDestroy: function (t, e) { }

    }), $.JQPlugin = {
        createPlugin: function (t, e) {
            "object" == typeof t && (e = t, t = "JQPlugin"), t = camelCase(t);
            var i = camelCase(e.name);
            JQClass.classes[i] = JQClass.classes[t].extend(e), new JQClass.classes[i]
        }
    }
})(jQuery);

/*! http://keith-wood.name/countdown.html
    Countdown for jQuery v2.1.0.
    Written by Keith Wood (wood.keith{at}optusnet.com.au) January 2008.
    Available under the MIT (http://keith-wood.name/licence.html) license.
    Please attribute the author if you use it. */

(function ($) { // Hide scope, no $ conflict
    'use strict';

    var pluginName = 'countdown';

    var Y = 0; // Years
    var O = 1; // Months
    var W = 2; // Weeks
    var D = 3; // Days
    var H = 4; // Hours
    var M = 5; // Minutes
    var S = 6; // Seconds

    $.JQPlugin.createPlugin({

        /** The name of the plugin.
            @default 'countdown' */
        name: pluginName,
        defaultOptions: {
            until: null,
            since: null,
            timezone: null,
            serverSync: null,
            format: 'dHMS',
            layout: '',
            compact: false,
            significant: 0,
            description: '',
            expiryUrl: '',
            expiryText: '',
            alwaysExpire: false,
            onExpiry: null,
            onTick: null,
            tickInterval: 1,
            style: null,
            circleStrokeWidth: 4,
            unitsInside: false,
            unitsPos: 'block',
            $countDown: null
        },

        regionalOptions: { // Available regional settings, indexed by language/country code
            '': { // Default regional settings - English/US
                labels: premiumCountDownStrings.plural,
                labels1: premiumCountDownStrings.single,
                compactLabels: ['y', 'm', 'w', 'd'],
                whichLabels: null,
                digits: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                timeSeparator: ':',
                isRTL: false
            }
        },

        /* Class name for the right-to-left marker. */
        _rtlClass: pluginName + '-rtl',
        /* Class name for the countdown section marker. */
        _sectionClass: pluginName + '-section',
        /* Class name for the period amount marker. */
        _amountClass: pluginName + '-amount',
        /* Class name for the period name marker. */
        _periodClass: pluginName + '-period',
        /* Class name for the countdown row marker. */
        _rowClass: pluginName + '-row',
        /* Class name for the holding countdown marker. */
        _holdingClass: pluginName + '-holding',
        /* Class name for the showing countdown marker. */
        _showClass: pluginName + '-show',
        /* Class name for the description marker. */
        _descrClass: pluginName + '-descr',

        /* List of currently active countdown elements. */
        _timerElems: [],

        increment: 0,

        _init: function () {
            var self = this;
            this._super();
            this._serverSyncs = [];
            var now = (typeof Date.now === 'function' ? Date.now : function () { return new Date().getTime(); });
            var perfAvail = (window.performance && typeof window.performance.now === 'function');
            // Shared timer for all countdowns

            function timerCallBack(timestamp) {

                var drawStart = (timestamp < 1e12 ? // New HTML5 high resolution timer
                    (perfAvail ? (window.performance.now() + window.performance.timing.navigationStart) : now()) :
                    // Integer milliseconds since unix epoch
                    timestamp || now());

                if (drawStart - animationStartTime >= 1000) {

                    self._updateElems();
                    animationStartTime = drawStart;

                }
                requestAnimationFrame(timerCallBack);
            }

            var animationStartTime = now();

            requestAnimationFrame(timerCallBack);



        },

        UTCDate: function (tz, year, month, day, hours, mins, secs, ms) {
            if (typeof year === 'object' && year instanceof Date) {
                ms = year.getMilliseconds();
                secs = year.getSeconds();
                mins = year.getMinutes();
                hours = year.getHours();
                day = year.getDate();
                month = year.getMonth();
                year = year.getFullYear();
            }

            var d = new Date();
            d.setUTCFullYear(year);
            d.setUTCDate(1);
            d.setUTCMonth(month || 0);
            d.setUTCDate(day || 1);
            d.setUTCHours(hours || 0);
            d.setUTCMinutes((mins || 0) - (Math.abs(tz) < 30 ? tz * 60 : tz));
            d.setUTCSeconds(secs || 0);
            d.setUTCMilliseconds(ms || 0);
            return d;
        },

        /** Convert a set of periods into seconds.
            Averaged for months and years.
            @param {number[]} periods The periods per year/month/week/day/hour/minute/second.
            @return {number} The corresponding number of seconds.
            @example var secs = $.countdown.periodsToSeconds(periods) */
        periodsToSeconds: function (periods) {
            return periods[0] * 31557600 + periods[1] * 2629800 + periods[2] * 604800 +
                periods[3] * 86400 + periods[4] * 3600 + periods[5] * 60 + periods[6];
        },

        _instSettings: function (elem, options) { // jshint unused:false
            return { _periods: [0, 0, 0, 0, 0, 0, 0] };
        },

        /** Add an element to the list of active ones.
            @private
            @param {Element} elem The countdown element. */
        _addElem: function (elem) {
            if (!this._hasElem(elem)) {
                this._timerElems.push(elem);
            }
        },

        /** See if an element is in the list of active ones.
            @private
            @param {Element} elem The countdown element.
            @return {boolean} <code>true</code> if present, <code>false</code> if not. */
        _hasElem: function (elem) {
            return ($.inArray(elem, this._timerElems) > -1);
        },

        /** Remove an element from the list of active ones.
            @private
            @param {Element} elem The countdown element. */
        _removeElem: function (elem) {
            this._timerElems = $.map(this._timerElems,
                function (value) { return (value === elem ? null : value); }); // delete entry
        },

        /** Update each active timer element.
            @private */
        _updateElems: function () {

            for (var i = this._timerElems.length - 1; i >= 0; i--) {
                this._updateCountdown(this._timerElems[i]);
            }
        },

        _optionsChanged: function (elem, inst, options) {
            if (options.layout) {
                options.layout = options.layout.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
            }
            this._resetExtraLabels(inst.options, options);
            var timezoneChanged = (inst.options.timezone !== options.timezone);
            $.extend(inst.options, options);
            this._adjustSettings(elem, inst,
                !this._eqNull(options.until) || !this._eqNull(options.since) || timezoneChanged);
            var now = new Date();
            if ((inst._since && inst._since < now) || (inst._until && inst._until > now)) {
                this._addElem(elem[0]);
            }
            this._updateCountdown(elem, inst);
        },

        /** Redisplay the countdown with an updated display.
            @private
            @param {Element|jQuery} elem The containing element.
            @param {object} inst The current settings for this instance. */
        _updateCountdown: function (elem, inst) {

            elem = elem.jquery ? elem : $(elem);
            inst = inst || this._getInst(elem);

            var self = this;

            if (!inst) {
                return;
            }

            self._generateHTML(inst, elem);
            elem.toggleClass(self._rtlClass, inst.options.isRTL);

            if (inst._hold !== 'pause' && $.isFunction(inst.options.onTick)) {

                var periods = inst._periods;

                if (inst.options.tickInterval === 1 ||
                    this.periodsToSeconds(periods) % inst.options.tickInterval === 0) {
                    // inst.options.onTick.apply(elem[0], [periods]);
                }

            }

            var expired = inst._hold !== 'pause' &&
                (inst._since ? inst._now.getTime() < inst._since.getTime() :
                    inst._now.getTime() >= inst._until.getTime());


            if (expired && !inst._expiring) {
                inst._expiring = true;
                if (this._hasElem(elem[0]) || inst.options.alwaysExpire) {
                    this._removeElem(elem[0]);
                    if ($.isFunction(inst.options.onExpiry)) {
                        inst.options.onExpiry.apply(elem[0], []);
                    }
                    if (inst.options.expiryText) {
                        var layout = inst.options.layout;
                        inst.options.layout = inst.options.expiryText;
                        this._updateCountdown(elem[0], inst);
                        inst.options.layout = layout;
                    }
                    if (inst.options.expiryUrl) {
                        window.location = inst.options.expiryUrl;
                    }
                }

                inst._expiring = false;
            }
        },

        /** Reset any extra labelsn and compactLabelsn entries if changing labels.
            @private
            @param {object} base The options to be updated.
            @param {object} options The new option values. */
        _resetExtraLabels: function (base, options) {
            var n = null;
            for (n in options) {
                if (n.match(/[Ll]abels[02-9]|compactLabels1/)) {
                    base[n] = options[n];
                }
            }
            for (n in base) { // Remove custom numbered labels
                if (n.match(/[Ll]abels[02-9]|compactLabels1/) && typeof options[n] === 'undefined') {
                    base[n] = null;
                }
            }
        },

        /** Determine whether or not a value is equivalent to <code>null</code>.
            @private
            @param {object} value The value to test.
            @return {boolean} <code>true</code> if equivalent to <code>null</code>, <code>false</code> if not. */
        _eqNull: function (value) {
            return typeof value === 'undefined' || value === null;
        },


        /** Calculate internal settings for an instance.
            @private
            @param {jQuery} elem The containing element.
            @param {object} inst The current settings for this instance.
            @param {boolean} recalc <code>true</code> if until or since are set. */
        _adjustSettings: function (elem, inst, recalc) {
            var serverEntry = null;
            for (var i = 0; i < this._serverSyncs.length; i++) {
                if (this._serverSyncs[i][0] === inst.options.serverSync) {
                    serverEntry = this._serverSyncs[i][1];
                    break;
                }
            }
            var now = null;
            var serverOffset = null;
            if (!this._eqNull(serverEntry)) {
                now = new Date();
                serverOffset = (inst.options.serverSync ? serverEntry : 0);
            }
            else {
                var serverResult = ($.isFunction(inst.options.serverSync) ?
                    inst.options.serverSync.apply(elem[0], []) : null);
                now = new Date();
                serverOffset = (serverResult ? now.getTime() - serverResult.getTime() : 0);
                this._serverSyncs.push([inst.options.serverSync, serverOffset]);
            }
            var timezone = inst.options.timezone;
            timezone = (this._eqNull(timezone) ? -now.getTimezoneOffset() : timezone);
            if (recalc || (!recalc && this._eqNull(inst._until) && this._eqNull(inst._since))) {
                inst._since = inst.options.since;
                if (!this._eqNull(inst._since)) {
                    inst._since = this.UTCDate(timezone, this._determineTime(inst._since, null));
                    if (inst._since && serverOffset) {
                        inst._since.setMilliseconds(inst._since.getMilliseconds() + serverOffset);
                    }
                }
                inst._until = this.UTCDate(timezone, this._determineTime(inst.options.until, now));
                if (serverOffset) {
                    inst._until.setMilliseconds(inst._until.getMilliseconds() + serverOffset);
                }
            }
            inst._show = this._determineShow(inst);
        },

        _preDestroy: function (t, e) {
            this._removeElem(t[0]), t.empty()
        },

        /** Pause or resume a countdown widget.
            @private
            @param {Element} elem The containing element.
            @param {string} hold The new hold setting. */
        _hold: function (elem, hold) {
            var inst = $.data(elem, this.name);
            if (inst) {
                if (inst._hold === 'pause' && !hold) {
                    inst._periods = inst._savePeriods;
                    var sign = (inst._since ? '-' : '+');
                    inst[inst._since ? '_since' : '_until'] =
                        this._determineTime(sign + inst._periods[0] + 'y' +
                            sign + inst._periods[1] + 'o' + sign + inst._periods[2] + 'w' +
                            sign + inst._periods[3] + 'd' + sign + inst._periods[4] + 'h' +
                            sign + inst._periods[5] + 'm' + sign + inst._periods[6] + 's');
                    this._addElem(elem);
                }
                inst._hold = hold;
                inst._savePeriods = (hold === 'pause' ? inst._periods : null);
                $.data(elem, this.name, inst);
                this._updateCountdown(elem, inst);
            }
        },

        /** Return the current time periods, broken down by years, months, weeks, days, hours, minutes, and seconds.
            @param {Element} elem The containing element.
            @return {number[]} The current periods for the countdown.
            @example var periods = $(selector).countdown('getTimes') */
        getTimes: function (elem) {
            var inst = $.data(elem, this.name);
            return (!inst ? null : (inst._hold === 'pause' ? inst._savePeriods : (!inst._hold ? inst._periods :
                this._calculatePeriods(inst, inst._show, inst.options.significant, new Date()))));
        },

        /** A time may be specified as an exact value or a relative one.
            @private
            @param {string|number|Date} setting The date/time value as a relative or absolute value.
            @param {Date} defaultTime The date/time to use if no other is supplied.
            @return {Date} The corresponding date/time. */
        _determineTime: function (setting, defaultTime) {
            var self = this;
            var offsetNumeric = function (offset) { // e.g. +300, -2
                var time = new Date();
                time.setTime(time.getTime() + offset * 1000);
                return time;
            };
            var offsetString = function (offset) { // e.g. '+2d', '-4w', '+3h +30m'
                offset = offset.toLowerCase();
                var time = new Date();
                var year = time.getFullYear();
                var month = time.getMonth();
                var day = time.getDate();
                var hour = time.getHours();
                var minute = time.getMinutes();
                var second = time.getSeconds();
                var pattern = /([+-]?[0-9]+)\s*(s|m|h|d|w|o|y)?/g;
                var matches = pattern.exec(offset);
                while (matches) {
                    switch (matches[2] || 's') {
                        case 's':
                            second += parseInt(matches[1], 10);
                            break;
                        case 'm':
                            minute += parseInt(matches[1], 10);
                            break;
                        case 'h':
                            hour += parseInt(matches[1], 10);
                            break;
                        case 'd':
                            day += parseInt(matches[1], 10);
                            break;
                        case 'w':
                            day += parseInt(matches[1], 10) * 7;
                            break;
                        case 'o':
                            month += parseInt(matches[1], 10);
                            day = Math.min(day, self._getDaysInMonth(year, month));
                            break;
                        case 'y':
                            year += parseInt(matches[1], 10);
                            day = Math.min(day, self._getDaysInMonth(year, month));
                            break;
                    }
                    matches = pattern.exec(offset);
                }
                return new Date(year, month, day, hour, minute, second, 0);
            };
            var time = (this._eqNull(setting) ? defaultTime :
                (typeof setting === 'string' ? offsetString(setting) :
                    (typeof setting === 'number' ? offsetNumeric(setting) : setting)));
            if (time) {
                time.setMilliseconds(0);
            }
            return time;
        },

        /** Determine the number of days in a month.
            @private
            @param {number} year The year.
            @param {number} month The month.
            @return {number} The days in that month. */
        _getDaysInMonth: function (year, month) {
            return 32 - new Date(year, month, 32).getDate();
        },

        /** Default implementation to determine which set of labels should be used for an amount.
            Use the <code>labels</code> attribute with the same numeric suffix (if it exists).
            @private
            @param {number} num The amount to be displayed.
            @return {number} The set of labels to be used for this amount. */
        _normalLabels: function (num) {
            return num;
        },


        _generateHTML: function (inst, elem) {

            // Determine what to show
            inst._periods = (inst._hold ? inst._periods : this._calculatePeriods(inst, inst._show, inst.options.significant, new Date()));

            // Show all 'asNeeded' after first non-zero value
            var shownNonZero = false;
            var showCount = 0;
            var sigCount = inst.options.significant;
            var show = $.extend({}, inst._show);
            var period = null;

            for (period = Y; period <= S; period++) {
                shownNonZero = shownNonZero || (inst._show[period] === '?' && inst._periods[period] > 0);
                show[period] = (inst._show[period] === '?' && !shownNonZero ? null : inst._show[period]);
                showCount += (show[period] ? 1 : 0);
                sigCount -= (inst._periods[period] > 0 ? 1 : 0);
            }

            var showSignificant = [false, false, false, false, false, false, false];
            for (period = S; period >= Y; period--) { // Determine significant periods
                if (inst._show[period]) {
                    if (inst._periods[period]) {
                        showSignificant[period] = true;
                    }
                    else {
                        showSignificant[period] = sigCount > 0;
                        sigCount--;
                    }
                }
            }

            var countDownHTML = null;

            var shouldRender = inst.options.$countDown.find('.countdown-amount').length < 1;

            if (inst.options.layout) {

                countDownHTML = this._buildLayout(inst, show, inst.options.layout, inst.options.compact, inst.options.significant, showSignificant);

                elem.html(countDownHTML);

            } else if (shouldRender || 'circle' === inst.options.style) {

                countDownHTML = '<div class="' + this._rowClass + ' ' + this._showClass + (inst.options.significant || showCount) +
                    (inst._hold ? ' ' + this._holdingClass : '') + '">' +
                    this.showFull(inst, show, Y) +
                    this.showFull(inst, show, O) +
                    this.showFull(inst, show, W) +
                    this.showFull(inst, show, D) +
                    this.showFull(inst, show, H) +
                    this.showFull(inst, show, M) +
                    this.showFull(inst, show, S) +
                    '</div>';

                elem.html(countDownHTML);

                if ('block' === inst.options.unitsPos) {
                    var circleSize = inst.options.$countDown.find('.countdown-amount').outerWidth() || 100;
                    inst.options.$countDown.find('.countdown-period span').css('width', circleSize);
                }


                shouldRender = false;

            } else {
                this.updateCountDownValues(inst, show, elem);
            }

            setTimeout(function () {
                inst.options.$countDown.removeClass('premium-addons__v-hidden');
            }, 1000);


        },

        showFull: function (inst, show, period) {

            var self = this;

            if ('circle' === inst.options.style) {

                var circleRadius = this._mapNumbers(inst._periods);

            }

            var minDigits = 2;

            return ((!inst.options.significant && show[period]) ||
                (inst.options.significant && showSignificant[period]) ?
                '<div class="' + self._sectionClass + ' countdown-section-' + period + '">' +

                '<div class="countdown-digit-separator">' +
                '<div class="' + self._amountClass + '">' +

                '<span>' + self._minDigits(inst, inst._periods[period], minDigits) + '</span>' +


                (inst.options.unitsInside ? self.getLabel(inst, period) : '') +

                ('circle' === inst.options.style ? '<div class="countdown-circle-wrap"><svg class="countdown-svg"><path fill="none" stroke="#333" stroke-width="4" d="' + self._buildCirclePath(inst, 0, circleRadius[period]) + '"></path></svg></div>' : '') +

                '</div>' +

                ('' !== inst.options.timeSeparator ? '<div class="countdown_separator">' +
                    '<span>' + inst.options.timeSeparator + '</span>' +
                    '</div>' : '') +

                '</div>' +

                (!inst.options.unitsInside ? self.getLabel(inst, period) : '') +

                '</div>' : '');

        },

        updateCountDownValues: function (inst, show, elem) {
            var self = this;

            inst._periods.map(function (period, index) {
                if ((!inst.options.significant && show[index]) || (inst.options.significant && showSignificant[index])) {

                    var digitValue = self._minDigits(inst, inst._periods[index], 2),
                        previousDigitValue = elem.find('.countdown-section-' + index).find('.countdown-amount > span').text();

                    if (digitValue !== previousDigitValue) {

                        if ('rotate' === inst.options.style) {

                            elem.find('.countdown-section-' + index).find('.countdown-amount > span').prop({
                                'deg': 0
                            }).animate({
                                deg: 90
                            }, {
                                duration: 450,
                                step: function (now) {


                                    elem.find('.countdown-section-' + index).find('.countdown-amount > span').css({
                                        transform: "rotateY(" + now + "deg)"
                                    });


                                },
                                complete: function () {

                                    elem.find('.countdown-section-' + index).find('.countdown-amount > span').text(digitValue);

                                    elem.find('.countdown-section-' + index).find('.countdown-amount > span').prop({
                                        'deg2': 0
                                    }).animate({
                                        deg2: 90
                                    }, {
                                        duration: 450,
                                        step: function (now2) {


                                            elem.find('.countdown-section-' + index).find('.countdown-amount > span').css({
                                                transform: "rotateY(" + (now2 - 90) + "deg)"
                                            });
                                        },

                                    });
                                }
                            });

                        } else {
                            elem.find('.countdown-section-' + index).find('.countdown-amount > span').text(digitValue);
                        }

                    }


                }
            })

        },

        getLabel: function (inst, period) {

            var labels = inst.options.labels;

            var whichLabels = inst.options.whichLabels || this._normalLabels;

            var labelsNum = inst.options['labels' + whichLabels(inst._periods[period])];

            return '<div class="' +
                this._periodClass +
                '">' +
                '<span>' + (labelsNum ? labelsNum[period] : labels[period]) + '</span>' +
                '</div>'

        },

        _polarToCartesian: function (centerX, centerY, radius, angleInDegrees) {
            var angleInRadians = (angleInDegrees - 90) * Math.PI / 180.0;

            return {
                x: centerX + (radius * Math.cos(angleInRadians)),
                y: centerY + (radius * Math.sin(angleInRadians))
            };
        },

        _buildCirclePath: function (instance, startAngle, endAngle) {

            var circleSize = instance.options.$countDown.find('.countdown-amount').outerWidth() || 100,
                x = circleSize / 2,
                y = circleSize / 2,
                radius = x - (instance.options.circleStrokeWidth / 2),
                start = this._polarToCartesian(x, y, radius, endAngle),
                end = this._polarToCartesian(x, y, radius, startAngle),
                largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";

            var d = [
                "M", start.x, start.y,
                "A", radius, radius, 0, largeArcFlag, 0, end.x, end.y
            ].join(" ");

            return d;
        },

        _mapNumbers: function (digitsValues) {


            var mappedNumbers = [],
                digitsResetValue = [
                    365,
                    12,
                    30,
                    30, //We will consider week/month as the same unit.
                    24,
                    60,
                    60
                ];

            digitsValues.map(function (elem, index) {

                //If the current value is larger than reset value, then change the reset value.
                //For example: 98 day, 48 hour, etc.
                if (digitsValues[index] > digitsResetValue[index]) {
                    digitsResetValue[index] = digitsResetValue[index] * 3;
                }

                var mappedNumber = (elem - digitsResetValue[index]) * 360 / (0 - digitsResetValue[index]);

                mappedNumbers.push(mappedNumber);
            });

            return mappedNumbers;

        },

        /** Construct a custom layout.
            @private
            @param {object} inst The current settings for this instance.
            @param {boolean[]} show Flags indicating which periods are requested.
            @param {string} layout The customised layout.
            @param {boolean} compact <code>true</code> if using compact labels.
            @param {number} significant The number of periods with values to show, zero for all.
            @param {boolean[]} showSignificant Other periods to show for significance.
            @return {string} The custom HTML. */
        _buildLayout: function (inst, show, layout, compact, significant, showSignificant) {
            var labels = inst.options[compact ? 'compactLabels' : 'labels'];
            var whichLabels = inst.options.whichLabels || this._normalLabels;
            var labelFor = function (index) {
                return (inst.options[(compact ? 'compactLabels' : 'labels') +
                    whichLabels(inst._periods[index])] || labels)[index];
            };
            var digit = function (value, position) {
                return inst.options.digits[Math.floor(value / position) % 10];
            };
            var subs = {
                desc: inst.options.description, sep: inst.options.timeSeparator,
                yl: labelFor(Y), yn: this._minDigits(inst, inst._periods[Y], 1),
                ynn: this._minDigits(inst, inst._periods[Y], 2),
                ynnn: this._minDigits(inst, inst._periods[Y], 3), y1: digit(inst._periods[Y], 1),
                y10: digit(inst._periods[Y], 10), y100: digit(inst._periods[Y], 100),
                y1000: digit(inst._periods[Y], 1000),
                ol: labelFor(O), on: this._minDigits(inst, inst._periods[O], 1),
                onn: this._minDigits(inst, inst._periods[O], 2),
                onnn: this._minDigits(inst, inst._periods[O], 3), o1: digit(inst._periods[O], 1),
                o10: digit(inst._periods[O], 10), o100: digit(inst._periods[O], 100),
                o1000: digit(inst._periods[O], 1000),
                wl: labelFor(W), wn: this._minDigits(inst, inst._periods[W], 1),
                wnn: this._minDigits(inst, inst._periods[W], 2),
                wnnn: this._minDigits(inst, inst._periods[W], 3), w1: digit(inst._periods[W], 1),
                w10: digit(inst._periods[W], 10), w100: digit(inst._periods[W], 100),
                w1000: digit(inst._periods[W], 1000),
                dl: labelFor(D), dn: this._minDigits(inst, inst._periods[D], 1),
                dnn: this._minDigits(inst, inst._periods[D], 2),
                dnnn: this._minDigits(inst, inst._periods[D], 3), d1: digit(inst._periods[D], 1),
                d10: digit(inst._periods[D], 10), d100: digit(inst._periods[D], 100),
                d1000: digit(inst._periods[D], 1000),
                hl: labelFor(H), hn: this._minDigits(inst, inst._periods[H], 1),
                hnn: this._minDigits(inst, inst._periods[H], 2),
                hnnn: this._minDigits(inst, inst._periods[H], 3), h1: digit(inst._periods[H], 1),
                h10: digit(inst._periods[H], 10), h100: digit(inst._periods[H], 100),
                h1000: digit(inst._periods[H], 1000),
                ml: labelFor(M), mn: this._minDigits(inst, inst._periods[M], 1),
                mnn: this._minDigits(inst, inst._periods[M], 2),
                mnnn: this._minDigits(inst, inst._periods[M], 3), m1: digit(inst._periods[M], 1),
                m10: digit(inst._periods[M], 10), m100: digit(inst._periods[M], 100),
                m1000: digit(inst._periods[M], 1000),
                sl: labelFor(S), sn: this._minDigits(inst, inst._periods[S], 1),
                snn: this._minDigits(inst, inst._periods[S], 2),
                snnn: this._minDigits(inst, inst._periods[S], 3), s1: digit(inst._periods[S], 1),
                s10: digit(inst._periods[S], 10), s100: digit(inst._periods[S], 100),
                s1000: digit(inst._periods[S], 1000)
            };
            var html = layout;
            // Replace period containers: {p<}...{p>}
            for (var i = Y; i <= S; i++) {
                var period = 'yowdhms'.charAt(i);
                var re = new RegExp('\\{' + period + '<\\}([\\s\\S]*)\\{' + period + '>\\}', 'g');
                html = html.replace(re, ((!significant && show[i]) ||
                    (significant && showSignificant[i]) ? '$1' : ''));
            }
            // Replace period values: {pn}
            $.each(subs, function (n, v) {
                var re = new RegExp('\\{' + n + '\\}', 'g');
                html = html.replace(re, v);
            });
            return html;
        },

        /** Ensure a numeric value has at least n digits for display.
            @private
            @param {object} inst The current settings for this instance.
            @param {number} value The value to display.
            @param {number} len The minimum length.
            @return {string} The display text. */
        _minDigits: function (inst, value, len) {
            value = '' + value;
            if (value.length >= len) {
                return this._translateDigits(inst, value);
            }
            value = '0000000000' + value;
            return this._translateDigits(inst, value.substr(value.length - len));
        },

        /** Translate digits into other representations.
            @private
            @param {object} inst The current settings for this instance.
            @param {string} value The text to translate.
            @return {string} The translated text. */
        _translateDigits: function (inst, value) {
            return ('' + value).replace(/[0-9]/g, function (digit) {
                return inst.options.digits[digit];
            });
        },

        /** Translate the format into flags for each period.
            @private
            @param {object} inst The current settings for this instance.
            @return {string[]} Flags indicating which periods are requested (?) or
                    required (!) by year, month, week, day, hour, minute, second. */
        _determineShow: function (inst) {
            var format = inst.options.format;
            var show = [];
            show[Y] = (format.match('y') ? '?' : (format.match('Y') ? '!' : null));
            show[O] = (format.match('o') ? '?' : (format.match('O') ? '!' : null));
            show[W] = (format.match('w') ? '?' : (format.match('W') ? '!' : null));
            show[D] = (format.match('d') ? '?' : (format.match('D') ? '!' : null));
            show[H] = (format.match('h') ? '?' : (format.match('H') ? '!' : null));
            show[M] = (format.match('m') ? '?' : (format.match('M') ? '!' : null));
            show[S] = (format.match('s') ? '?' : (format.match('S') ? '!' : null));
            return show;
        },

        /** Calculate the requested periods between now and the target time.
            @private
            @param {object} inst The current settings for this instance.
            @param {string[]} show Flags indicating which periods are requested/required.
            @param {number} significant The number of periods with values to show, zero for all.
            @param {Date} now The current date and time.
            @return {number[]} The current time periods (always positive)
                    by year, month, week, day, hour, minute, second. */
        _calculatePeriods: function (inst, show, significant, now) {
            // Find endpoints
            inst._now = now;
            inst._now.setMilliseconds(0);
            var until = new Date(inst._now.getTime());
            if (inst._since) {
                if (now.getTime() < inst._since.getTime()) {
                    inst._now = now = until;
                }
                else {
                    now = inst._since;
                }
            }
            else {
                until.setTime(inst._until.getTime());
                if (now.getTime() > inst._until.getTime()) {
                    inst._now = now = until;
                }
            }
            // Calculate differences by period
            var periods = [0, 0, 0, 0, 0, 0, 0];
            if (show[Y] || show[O]) {
                // Treat end of months as the same
                var lastNow = this._getDaysInMonth(now.getFullYear(), now.getMonth());
                var lastUntil = this._getDaysInMonth(until.getFullYear(), until.getMonth());
                var sameDay = (until.getDate() === now.getDate() ||
                    (until.getDate() >= Math.min(lastNow, lastUntil) &&
                        now.getDate() >= Math.min(lastNow, lastUntil)));
                var getSecs = function (date) {
                    return (date.getHours() * 60 + date.getMinutes()) * 60 + date.getSeconds();
                };
                var months = Math.max(0,
                    (until.getFullYear() - now.getFullYear()) * 12 + until.getMonth() - now.getMonth() +
                    ((until.getDate() < now.getDate() && !sameDay) ||
                        (sameDay && getSecs(until) < getSecs(now)) ? -1 : 0));
                periods[Y] = (show[Y] ? Math.floor(months / 12) : 0);
                periods[O] = (show[O] ? months - periods[Y] * 12 : 0);
                // Adjust for months difference and end of month if necessary
                now = new Date(now.getTime());
                var wasLastDay = (now.getDate() === lastNow);
                var lastDay = this._getDaysInMonth(now.getFullYear() + periods[Y],
                    now.getMonth() + periods[O]);
                if (now.getDate() > lastDay) {
                    now.setDate(lastDay);
                }
                now.setFullYear(now.getFullYear() + periods[Y]);
                now.setMonth(now.getMonth() + periods[O]);
                if (wasLastDay) {
                    now.setDate(lastDay);
                }
            }
            var diff = Math.floor((until.getTime() - now.getTime()) / 1000);
            var period = null;
            var extractPeriod = function (period, numSecs) {
                periods[period] = (show[period] ? Math.floor(diff / numSecs) : 0);
                diff -= periods[period] * numSecs;
            };
            extractPeriod(W, 604800);
            extractPeriod(D, 86400);
            extractPeriod(H, 3600);
            extractPeriod(M, 60);
            extractPeriod(S, 1);
            if (diff > 0 && !inst._since) { // Round up if left overs
                var multiplier = [1, 12, 4.3482, 7, 24, 60, 60];
                var lastShown = S;
                var max = 1;
                for (period = S; period >= Y; period--) {
                    if (show[period]) {
                        if (periods[lastShown] >= max) {
                            periods[lastShown] = 0;
                            diff = 1;
                        }
                        if (diff > 0) {
                            periods[period]++;
                            diff = 0;
                            lastShown = period;
                            max = 1;
                        }
                    }
                    max *= multiplier[period];
                }
            }
            if (significant) { // Zero out insignificant periods
                for (period = Y; period <= S; period++) {
                    if (significant && periods[period]) {
                        significant--;
                    }
                    else if (!significant) {
                        periods[period] = 0;
                    }
                }
            }
            return periods;
        }
    });

})(jQuery);
