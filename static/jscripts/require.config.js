/**
 * Copyright (C) 2013 Andrey F. Kupreychik (Foxel)
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
    paths: {
        "jquery": "jquery-1.7.2.min",
        "jquery.ui": "jquery-ui-1.8.21.custom.min",
        "jquery.timepicker": "jquery-ui-timepicker-addon",
        "elrte": "elrte.min"
    },
    shim: {
        "bootstrap.transition": ["jquery"],
        "bootstrap.modal": ["jquery", "bootstrap.transition"],
        "bootstrap.tooltip": ["jquery", "bootstrap.transition"],
        "bootstrap.carousel": ["jquery", "bootstrap.transition"],
        "bootstrap.ie6": ["jquery"],
        "jquery.chosen": ["jquery"],
        "jquery.colorbox": ["jquery", "css!static/css/colorbox"],
        "jquery.tagcloud": ["jquery"],
        "jquery.ui": ["jquery", "css!static/css/jquery-ui.custom"],
        "jquery.timepicker": ["jquery.ui"],
        "elrte": ["jquery.ui", "css!static/css/elrte.min"],
        "i18n/elrte.ru": ["elrte"]
    },
    map: {
        '*': {
            'css': 'require.css'
        }
    },
    waitSeconds: 15
});
