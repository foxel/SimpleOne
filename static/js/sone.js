/**
 * Copyright (C) 2016 Andrey F. Kupreychik (Foxel)
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
define(['module', 'jquery', 'sone.misc'], function (module, $) {
    var SOne = {
        root: module.config().root || '/',
        config: module.config().config || {},

        prepareContent: function(block) {
            var jq = typeof block !== 'undefined'
                    ? function(selector) { return $(block).find(selector); }
                    : $;
            jq('select').sOneSelect();
            jq('time').timeFormat();
            jq('a.post-button').postButton();

            if (jq('textarea.htmleditor').length) {
                require(['sone.wysiwyg'], function () {
                    // create editor
                    jq('textarea.htmleditor').wysiwyg();
                });
            }
        }
    };

    $().ready(function() {
        SOne.prepareContent();
    });

    $.SOne = SOne;

    return SOne;
});
