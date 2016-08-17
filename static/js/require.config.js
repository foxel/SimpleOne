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
        "jquery": [
            "https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min",
            "https://code.jquery.com/jquery-1.12.4.min",
            "../libs/jquery/jquery.min"
        ],
        "jquery.ui": [
            "https://code.jquery.com/ui/1.8.24/jquery-ui.min",
            "../libs/jquery-ui/js/jquery-ui.min"
        ],
        "jquery.migrate": [
            "https://code.jquery.com/jquery-migrate-1.3.0.min",
            "../libs/jquery/jquery-migrate.min"
        ],
        "jquery.timepicker": "../libs/jquery-ui/js/jquery-ui-timepicker-addon",
        "jquery.colorbox": "../libs/colorbox/js/jquery.colorbox",
        "elrte": "../libs/elrte/js/elrte.min",
        "elrte.i18n": ["../libs/elrte/js/i18n/elrte.ru", "../libs/elrte/js/i18n/elrte.en"],
        "elfinder": "../libs/elfinder/js/elfinder.min",
        "elfinder.i18n": ["../libs/elfinder/js/i18n/elfinder.ru", "../libs/elfinder/js/i18n/elfinder.en"],
        "select2": "../libs/select2/js/select2",
        "select2.i18n": "../libs/select2/js/i18n/select2_locale_ru",
        /* bootstrap */
        "bootstrap": "../libs/bootstrap/js/bootstrap.min"
    },
    shim: {
        "bootstrap": ["jquery"],
        "jquery.ui": ["jquery", "css!static/libs/jquery-ui/css/jquery-ui.custom"],
        "jquery.migrate": ["jquery"],
        "jquery.chosen": ["jquery"],
        "jquery.colorbox": ["jquery", "css!static/libs/colorbox/css/colorbox"],
        "jquery.tagcloud": ["jquery"],
        "jquery.timepicker": ["jquery.ui"],
        "elrte": ["jquery.ui", "jquery.migrate", "css!static/libs/elrte/css/elrte.min"],
        "elrte.i18n": ["elrte"],
        "elfinder": ["jquery.ui", "jquery.migrate", "css!static/libs/elfinder/css/elfinder.full"],
        "elfinder.i18n": ["elfinder"],
        "select2": ["jquery", "css!static/libs/select2/css/select2", "css!static/libs/select2/css/select2-bootstrap"],
        "select2.i18n": ["select2"]
    },
    map: {
        '*': {
            'css': 'require.css'
        }
    },
    waitSeconds: 15
});
