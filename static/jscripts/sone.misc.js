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

String.prototype.format = function() {
    var args = arguments;
    return this.replace(/\%(\d+)/g, function(match, index) {
        return typeof args[index] != 'undefined'
            ? args[index]
            : match;
    });
};

define(['jquery', 'date.format', 'jquery.chosen', 'i18n/date.format.ru'], function($) {
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
                var time = new Date(Date.parse($(this).attr('datetime')));
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
        },
        imageModal: function(src) {
            return this.each(function() {
                var $this = $(this);
                if (!$this.closest('a').length) {
                    var modalDiv, i;

                    var showModal = function () {
                        var s = Math.min(($(window).width() - 130) / i.width, ($(window).height() * 0.85 - 30) / i.height, 1);
                        var w = Math.max(parseInt(i.width * s), 20);
                        var h = Math.max(parseInt(i.height * s), 18);
                        modalDiv.find('.modal-body').css({'max-height': h + 'px'});
                        w += 30;
                        h += 30;
                        modalDiv.css({
                            'width': w + 'px',
                            'margin-left': (-w / 2) + 'px'
                        });
                        modalDiv.modal('show');
                    };
                    var prepareModal = function (onReady) {
                        require(['bootstrap.modal'], function () {
                            var _src = src || $this.attr('src');
                            _src = _src.replace(/\?scale(&w=\d+)?(&h=\d+)?/, '?');
                            i = document.createElement('img');
                            $(i).load(function () {
                                modalDiv = $('<div />', {'class': 'modal fade hide'}).append(
                                    $('<div />', {'class': 'modal-body'}).append(i)
                                );
                                $('body').append(modalDiv);
                                modalDiv.modal({show: false});
                                onReady && onReady();
                            }).attr('src', _src);
                        });
                    };

                    $this.css({cursor: 'pointer'}).click(function () {
                        modalDiv ? showModal() : prepareModal(showModal);
                        return false;
                    });
                    require(['bootstrap.tooltip'], function () {
                        $this.tooltip({title: 'Увеличить'});
                    });
                }
            })
        }
    });
});
