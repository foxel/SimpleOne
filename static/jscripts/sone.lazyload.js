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

require.config({
    shim: {
        "jquery.lazyload": ["jquery"]
    }
});

define(["jquery", "jquery.lazyload"], function ($) {
    var lazyLoadOriginal = $.fn.lazyload;

    $.fn.lazyload = function(options) {
        var _options = {
            "data_attribute": "lazyload-src",
            "threshold" : 150,
            "event" : "scroll mouseover",
            "appear": function() {
                var $el = $(this);
                var src = $el.data("lazyload-src");
                src = src.replace(/\?scale(&w=\d+)?(&h=d+)?/, '?');

                if (!$el.closest("a").length) {
                    var modalDiv, i;

                    var showModal = function() {
                        var s = Math.min(($(window).width() - 130)/i.width, ($(window).height()*0.85 - 30)/i.height, 1);
                        var w = Math.max(parseInt(i.width*s), 20);
                        var h = Math.max(parseInt(i.height*s), 18);
                        modalDiv.find(".modal-body").css({"max-height": h + "px"});
                        w += 30;
                        h += 30;
                        modalDiv.css({
                            "width":       w + "px",
                            "margin-left": (-w/2) + "px"
                        });
                        modalDiv.modal("show");
                    };
                    var prepareModal = function() {
                        require(["bootstrap.modal", "bootstrap.tooltip"], function() {
                            i = document.createElement("img");
                            $(i).load(function () {
                                modalDiv = $("<div />", {"class": "modal fade hide"}).append(
                                    $("<div />", {"class": "modal-body"}).append(i)
                                );
                                $("body").append(modalDiv);
                                modalDiv.modal({show: false});
                                showModal();
                            }).attr("src", src);
                        });
                    };

                    $el.css({cursor: "pointer"}).click(function () {
                        modalDiv ? showModal() : prepareModal();
                        return false;
                    }).tooltip({title: "Просмотреть"});
                }
            }
        };

        if(options) {
            $.extend(_options, options);
        }

        lazyLoadOriginal.call($(this), _options);
    };

    return $.fn.lazyload;
});

