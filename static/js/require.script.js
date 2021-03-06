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
/* global require, document */
define(function() {
    var head = document.getElementsByTagName('head')[0];

    return {
        load: function(scriptPath, req, load, config) {
            var script = require.createNode(config);
            script.src = req.toUrl(scriptPath);
            head.appendChild(script);

            load(script);
        }
    }
});
