/**
 * Copyright (C) 2012 - 2013 Andrey F. Kupreychik (Foxel)
 *
 * This file is part of QuickFox SimpleOne.
 *
 * SimpleOne is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimpleOne is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimpleOne. If not, see <http://www.gnu.org/licenses/>.
 */

Date = (function(nativeDate) {

    if (typeof (nativeDate.prototype.toISOString) != 'function') {
        nativeDate.prototype.toISOString = nativeDate.prototype.toJSON;
    }

    var test = nativeDate.parse('2011-06-02T09:34:29+02:00');
    if (!test || +test !== 1307000069000) {
        var nativeParse = nativeDate.parse;

        var d;
        var rx = /^(\d{4}\-\d\d\-\d\d([tT][\d:\.]*)?)([zZ]|([+\-])(\d\d):(\d\d))?$/;
        nativeDate.parse = function(s) {
            if (s instanceof nativeDate || s instanceof d) {
                return s.valueOf();
            }

            var day, tz,
                p = rx.exec(s) || [];
            if (p[1]){
                day= p[1].split(/\D/);
                for (var i = 0, L = day.length; i < L; i++) {
                    day[i] = parseInt(day[i], 10) || 0;
                }
                day[1]-= 1;
                day= new nativeDate(nativeDate.UTC.apply(nativeDate, day));
                if (!day.getDate()) return NaN;
                if (p[5]) {
                    tz = (parseInt(p[5], 10)*60);
                    if (p[6]) {
                        tz += parseInt(p[6], 10);
                    }
                    if (p[4]== '+') {
                        tz *= -1;
                    }
                    if(tz) {
                        day.setUTCMinutes(day.getUTCMinutes() + tz);
                    }
                } else if (!p[3]) {
                    day.setUTCMinutes(day.getUTCMinutes() + day.getTimezoneOffset());
                }
                return day.valueOf();
            }
            return nativeParse.apply(this, arguments);
        };

        var prepare = function() {
            if (arguments.length) {
                if (arguments.length == 1) {
                    var date = arguments[0];
                    if (typeof date != 'number') {
                        date = nativeDate.parse.call(nativeDate, date);
                    }
                    return new nativeDate(date);
                } else {
                    var args = [];
                    for(var i in arguments) {
                        args.push('arguments['+i+']');
                    }
                    return eval('new nativeDate('+args.join(', ')+')');
                }
            }
            return new nativeDate();
        };

        d = function() {
            if (this instanceof d) {
                this.native = prepare.apply(this, arguments);
            } else {
                return nativeDate();
            }
        };

        var k,
            statics = ['parse', 'UTC', 'now'],
            methods = [
                'toLocaleString',
                'getDate',
                'getDay',
                'getFullYear',
                'getMilliseconds',
                'getMinutes',
                'getSeconds',
                'getTimezoneOffset',
                'getUTCDate',
                'getUTCDay',
                'getUTCHours',
                'getHours',
                'getUTCMilliseconds',
                'getUTCMinutes',
                'getUTCSeconds',
                'setSeconds',
                'setFullYear',
                'setMilliseconds',
                'setTime',
                'setYear',
                'setDate',
                'setUTCDate',
                'setUTCHours',
                'setHours',
                'setUTCMilliseconds',
                'setUTCMinutes',
                'setMinutes',
                'setMonth',
                'setUTCSeconds',
                'setUTCFullYear',
                'setUTCMonth',
                'toGMTString',
                'toLocaleFormat',
                'toLocaleTimeString',
                'toLocaleDateString',
                'toString',
                'toJSON',
                'toISOString',
                'toTimeString',
                'toDateString',
                'toUTCString',
                'getUTCFullYear',
                'getMonth',
                'getUTCMonth',
                'getTime',
                'valueOf',
                'getYear'
            ];


        while (k = statics.pop()) {
            if (nativeDate.hasOwnProperty(k)) {
                d[k] = (function(func, context) {
                    return function() {
                        return func.apply(context, arguments);
                    }
                })(nativeDate[k], nativeDate);
            }
        }

        while (k = methods.pop()) {
            if (nativeDate.prototype.hasOwnProperty(k)) {
                var nFunc = nativeDate.prototype[k];
                d.prototype[k] = (function(func) {
                    return function() {
                        return func.apply(this.native, arguments);
                    }
                })(nativeDate.prototype[k]);
            }
        }

        return d;
    }

    return nativeDate;
})(Date);
