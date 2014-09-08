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

define(['jquery', 'SOne', 'elrte', 'elrte.i18n'], function ($, SOne) {
    var SOneWYSIWYG = function(elem, options) {
        if (!elem.length) {
            return;
        }

        options || (options = {});
        if (typeof options == 'string') {
            options = {"toolbar": options};
        }
        options = $.extend({}, SOneWYSIWYG.config, SOne.config.wysiwyg||{}, options);


        $(elem).each(function () {
            if ($(this).val() == '') {
                $(this).val('<p>&nbsp;</p>');
            }
        }).elrte(options);
    };

    $.extend(SOneWYSIWYG, {
        config:{
            lang:'ru',
            styleWithCSS:false,
            allowTextNodes:false,
            height:400,
            panels:{
                'sone-media':['image', 'embedmedia'],
                'sone-elements':['horizontalrule', 'blockquote', 'div', 'pagebreak', 'stopfloat', 'css', 'nbsp'],
                'sone-format':['formatblock', 'fontsize'],

                'mini-copypaste':['copy', 'cut', 'paste', 'pastetext', 'removeformat', 'docstructure'],
                'mini-style':['formatblock', 'bold', 'italic', 'underline', 'strikethrough', 'css'],
                'mini-links':['link', 'unlink', 'image'],

                'micro-copypaste':['pastetext', 'removeformat', 'docstructure'],
                'micro-style':['bold', 'italic', 'underline', 'strikethrough']
            },
            toolbars:{
                'sone':['copypaste', 'elfinder', 'undoredo', 'style', 'alignment', 'colors', 'eol', 'sone-format', 'indent', 'lists', 'links', 'eol', 'sone-elements', 'sone-media', 'tables'],
                'sone-mini':['mini-copypaste', 'mini-style', 'colors', 'mini-links'],
                'sone-micro':['micro-copypaste', 'micro-style', 'mini-links']
            },
            toolbar: 'sone',
            resizable: true,
            resizeHandle: 's',
            cssfiles: [SOne.root+'static/css/bootstrap.css', SOne.root+'static/libs/elrte/css/elrte.inner.css']
        }
    });

    $.fn.extend({
        wysiwyg: function (options) {
            SOneWYSIWYG(this, options);
            return this;
        }
    });

    return SOneWYSIWYG;
});
