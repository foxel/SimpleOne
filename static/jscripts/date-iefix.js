Date = (function(nativeDate) {

    if (typeof (nativeDate.prototype.toISOString) != 'function') {
        nativeDate.prototype.toISOString = nativeDate.prototype.toJSON || function() {
          return this.getUTCFullYear() + "-"
            + ("0" + this.getUTCMonth() + 1 + "-").slice(-3)
            + ("0" + this.getUTCDate() + "T").slice(-3)
            + ("0" + this.getUTCHours() + ":").slice(-3)
            + ("0" + this.getUTCMinutes() + ":").slice(-3)
            + ("0" + this.getUTCSeconds() + ".").slice(-3)
            + ("00" + this.getUTCMilliseconds() + "Z").slice(-4);
        };
    }

    var test = nativeDate.parse('2011-06-02T09:34:29+02:00');
    if (!test || +test !== 1307000069000) {
        var nativeParse = nativeDate.parse;

        var d;
        var rx = /^(\d{4}\-\d\d\-\d\d([tT ][\d:\.]*)?)([zZ]|([+\-])(\d\d):(\d\d))?$/;
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
                }
                return day.valueOf();
            }
            return nativeParse.apply(this, arguments);
        };

        var prepare = function() {
            return arguments.length == 1
                ? new nativeDate(nativeDate.parse.apply(nativeDate, arguments))
                : new nativeDate()
        };

        d = function() {
            if (this instanceof d) {
                this.native = prepare.apply(this, arguments);
            } else {
                return prepare.apply(this, arguments);
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
