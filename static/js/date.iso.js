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

(function(nativeDate) {

    if (typeof (nativeDate.prototype.toISOString) != 'function') {
        nativeDate.prototype.toISOString = nativeDate.prototype.toJSON;
    }

    var rx = /^(\d{4}\-\d\d\-\d\d([tT][\d:\.]*)?)([zZ]|([+\-])(\d\d):(\d\d))?$/;
    var parseISOString = nativeDate.parseISOString = function(s) {
        var day, tz,
            p = rx.exec(s) || [];
        if (p[1]) {
            day = p[1].split(/\D/);
            for (var i = 0, L = day.length; i < L; i++) {
                day[i] = parseInt(day[i], 10) || 0;
            }
            day[1] -= 1;
            day = new nativeDate(nativeDate.UTC.apply(nativeDate, day));
            if (!day.getDate()) return NaN;
            if (p[5]) {
                tz = (parseInt(p[5], 10) * 60);
                if (p[6]) {
                    tz += parseInt(p[6], 10);
                }
                if (p[4] == '+') {
                    tz *= -1;
                }
                if (tz) {
                    day.setUTCMinutes(day.getUTCMinutes() + tz);
                }
            } else if (!p[3]) {
                day.setUTCMinutes(day.getUTCMinutes() + day.getTimezoneOffset());
            }
            return day.valueOf();
        }
        return NaN;
    };

    var test = nativeDate.parse('2011-06-02T09:34:29+02:00');
    if (!test || +test !== 1307000069000) {
        var nativeParse = nativeDate.parse;

        nativeDate.parse = function(s) {
            if (s instanceof nativeDate) {
                return s.valueOf();
            }

            var t = parseISOString(s);
            if (!isNaN(t)) {
                return t;
            }

            return nativeParse.apply(this, arguments);
        };
    }
})(Date);
