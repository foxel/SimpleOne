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

define(["jquery", "sone.misc", "jquery.lazyload"], function ($) {
    var lazyLoadOriginal = $.fn.lazyload;

    $.fn.lazyload = function(options) {
        var _options = {
            "data_attribute": "lazyload-src",
            "threshold" : 150,
            "event" : "scroll mouseover",
            "appear": function() {
                var $el = $(this);
                var src = $el.data("lazyload-src");
                $el.imageModal(src);
            }
        };

        if(options) {
            $.extend(_options, options);
        }

        lazyLoadOriginal.call($(this), _options);
    };

    return $.fn.lazyload;
});

