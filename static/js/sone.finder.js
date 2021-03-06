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

define(['jquery', 'SOne', 'elfinder', 'elfinder.i18n'], function ($, SOne) {
    var SOneFinder = function(options) {
        options || (options = {});
        if (typeof options == 'string') {
            options = {"url": options};
        }
        options = $.extend({}, SOneFinder.config, SOne.config.finder||{}, {
            width:  $(window).width()-200,
            height: Math.max(400, $(window).height()-80)
        }, options);

        $('<div />').dialogelfinder(options).zIndex(1100);
    };

    $.extend(SOneFinder, {
        config: {
            lang:   'ru',
            commandsOptions: {
                getfile: {
                    onlyURL:    true,
                    oncomplete: 'destroy'
                },
                help: {view: ['shortcuts', null, null]}
            },
            uiOptions:{
                // toolbar configuration
                toolbar:[
                    ['back', 'forward'],
                    ['home', 'up'],
                    ['mkdir', 'upload'],
                    ['open', 'download', 'getfile'],
                    ['info'],
                    ['quicklook'],
                    ['copy', 'cut', 'paste'],
                    ['rm'],
                    ['duplicate', 'rename', 'resize'],
                    ['search'],
                    ['view', 'sort'],
                    ['help']
                ]
            },
            cwd: {oldSchool: true},
            destroyOnClose: true
        }
    });

    return SOneFinder;
});
