/**
 * Copyright (c) 2013-2016, Erin Morelli.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 * EM Beer Manager admin javascript functions
 */

/*global
    jQuery
*/
/*jslint
    browser: true
    unparam: true
*/

jQuery(document).ready(function ($) {
    'use strict';

    // Keep page at top on tab change
    function stayAtTop() {
        // Execute immediately
        window.scrollTo(0, 0);

        // Delay for browser compatibility
        setTimeout(function () {
            window.scrollTo(0, 0);
        }, 1);
    }

    // Check for a hash in the URL
    if (location.hash) {
        // Don't jump to div
        stayAtTop();

        // Get hash without #
        var hash = location.hash.slice(1);

        // Add/remove active classes
        if (hash !== '') {
            $('#embm-settings--tabs').find('.nav-tab').removeClass('nav-tab-active');
            $('#embm-settings--tabs').find('.nav-tab-' + hash).addClass('nav-tab-active');
        }
    }

    // Setup jquery ui tabs
    $('#embm-settings--tabs').tabs({
        activate: function (ignore, ui) {
            // Don't jump to div
            stayAtTop();

            // Get tab links
            var new_tab = ui.newTab.find('.nav-tab'),
                old_tab = ui.oldTab.find('.nav-tab'),
                new_hash = new_tab[0].getAttribute("href");

            // Toggle active classes
            new_tab.toggleClass('nav-tab-active');
            old_tab.toggleClass('nav-tab-active');

            // Reset URL hash
            location.hash = new_hash;
        }
    });

    // Styles reset redirect
    $('button.embm-settings--styles-button').on('click', function (e) {
        e.preventDefault();
        location.search = '?page=embm-settings&embm-styles-reset=1';
    });

    // Handle "NO" button press on reset page
    $('.embm-settings--styles-form input[name="No"]').on('click', function (e) {
        e.preventDefault();
        location.search = '?page=embm-settings';
    });

    // Clean up URL after notice dismissal
    $('.embm-settings--styles-notice.notice button.notice-dismiss').on('click', function (e) {
        e.preventDefault();

        // Set vars
        var $el = $('.embm-settings--styles-notice.notice'), // Notice container
            url = window.location.href, // Full URL
            page = url.substring(url.lastIndexOf('/') + 1), // Page URL
            clean_url = page.split('?')[0] + '?' + $.param({page: 'embm-settings'}); // Reset URL

        // Remove notice
        $el.fadeTo(100, 0, function () {
            $el.slideUp(100, function () {
                $el.remove();
            });
        });

        // Update URL
        window.history.replaceState(null, null, clean_url);
    });
});
