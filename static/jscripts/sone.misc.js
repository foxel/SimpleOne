String.prototype.format = function() {
    var args = arguments;
    return this.replace(/\%(\d+)/g, function(match, index) {
        return typeof args[index] != 'undefined'
            ? args[index]
            : match;
    });
};

(function($) {
    $.fn.extend({
        sOneSelect: function (options) {
            if ($.browser.msie && ($.browser.version === "6.0" || ($.browser.version === "7.0" && document.documentMode === 7))) {
                return this;
            }
            return this.each(function (input_field) {
                var $this = $(this);
                var emptyValue = $('option[value=""]', this).text();
                if (emptyValue) {
                    $this.attr('data-placeholder', emptyValue);
                    $('option[value=""]', this).text('');
                }
                $this.chosen({allow_single_deselect: true});
            });
        },
        timeFormat: function (options) {
            var now = Math.floor((new Date()).valueOf()/1000);
            var today = new Date();
            today.setHours(0);
            today.setMinutes(0);
            today.setSeconds(0);
            today = Math.floor(today.valueOf()/1000);
            var yesterday = today - 3600*24;
            var tomorrow = today + 3600*24;

            var updateInOneMinute = $();
            var updateInOneHour = $();
            var res = this.each(function (input_field) {
                var time = new Date($(this).attr('datetime'));
                var ts = Math.floor(time.valueOf()/1000);
                if (isNaN(ts)) {
                    return;
                }

                var text = time.format('dd mmm yyyy HH:MM');
                if (ts >= now) {
                    if (ts < now + 3600) {
                        if (ts < now + 60) {
                            text = 'меньше чем через минуту';
                        } else {
                            text = 'через %0 мин.'.format(Math.round((ts - now)/60));
                        }
                    } else if (ts < tomorrow) {
                        text = 'сегодня %0'.format(time.format('HH:MM'));
                    }
                    if (ts < now + 3600*2) {
                        updateInOneMinute.push(this);
                    } else {
                        updateInOneHour.push(this);
                    }
                } else if (ts > now - 3600) {
                    if (ts > now - 60) {
                        text = 'меньше минуты назад';
                    } else {
                        text = '%0 мин. назад'.format(Math.round((now - ts)/60));
                    }
                    updateInOneMinute.push(this);
                } else if (ts > yesterday) {
                    if (ts > now - 3*3600) {
                        text = '%0 ч. назад'.format(Math.round((now - ts)/3600));
                    } else if (ts > today) {
                        text = 'сегодня %0'.format(time.format('HH:MM'));
                    } else {
                        text = 'вчера %0'.format(time.format('HH:MM'));
                    }
                    updateInOneHour.push(this);
                }

                $(this).text(text);
            });

            if (updateInOneMinute.length) {
                setTimeout(function() {
                    updateInOneMinute.timeFormat();
                }, 60*1000);
            }
            if (updateInOneHour.length) {
                setTimeout(function() {
                    updateInOneHour.timeFormat();
                }, 3600*1000);
            }

            return res;
        }
    });
})(jQuery);