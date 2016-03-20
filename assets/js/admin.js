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
    jQuery,
    embm_settings
*/
/*jslint
    browser: true
    unparam: true
*/

jQuery(document).ready(function ($) {
    'use strict';

    // Check for a hash in the URL
    if (location.hash) {
        // Don't jump to div
        window.scrollTo(0, 0);

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
            // Get tab links
            var new_tab = ui.newTab.find('.nav-tab'),
                old_tab = ui.oldTab.find('.nav-tab'),
                new_hash = new_tab[0].getAttribute("href");

            // Toggle active classes
            new_tab.toggleClass('nav-tab-active');
            old_tab.toggleClass('nav-tab-active');

            // Reset URL hash
            location.hash = new_hash;

            // Don't jump to div
            window.scrollTo(0, 0);
        }
    });

    // Prevent jumping to divs on tab clicks
    $('.embm-nav-tab').on('click', function (e) {
        e.preventDefault();
        return false;
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
    $('.embm-settings--notice.notice button.notice-dismiss').on('click', function (e) {
        e.preventDefault();

        // Set vars
        var $el = $('.embm-settings--notice.notice'), // Notice container
            url = window.location.href, // Full URL
            url_hash = window.location.hash, // URL hash
            page = url.substring(url.lastIndexOf('/') + 1), // Page URL
            clean_url = page.split('?')[0] + '?' + $.param({page: 'embm-settings'}) + url_hash; // Reset URL

        console.log('click', clean_url);

        // Remove notice
        $el.fadeTo(100, 0, function () {
            $el.slideUp(100, function () {
                $el.remove();
            });
        });

        // Update URL
        window.history.replaceState(null, null, clean_url);
    });

    // Toggle contextual help for '?' link clicks
    $('.embm-settings--help').on('click', function (e) {
        // Get tab name from link
        var tab = $(this).data('help');

        // Remove 'active' class from all link tabs
        $('li[id^="tab-link-"]').each(function () {
            $(this).removeClass('active');
        });

        // Hide all panels
        $('div[id^="tab-panel-"]').each(function () {
            $(this).css('display', 'none');
        });

        // Set our desired link/panel
        $('#tab-link-' + tab).addClass('active');
        $('#tab-panel-' + tab).css('display', 'block');

        // Force click on the Help tab
        $('#contextual-help-link').click();
    });

    // Select icon option
    $('#embm_untappd_icons').val(embm_settings.options.embm_untappd_icons);

    // Toggle icon image on select change
    $('.embm-settings--untappd-select').on('change', function (e) {
        // Get URL for selected image
        var img_src = embm_settings.plugin_url + 'assets/img/checkin-button-' + this.value + '.png';

        // Update icon image source
        $('.embm-settings--untappd-icon').attr('src', img_src);
    });

    /* ---- LABS ---- */

    // Redirect to Untappd to authorize user
    $('button.embm-labs--authorize-button').on('click', function (e) {
        var url = window.location.href, // Full URL
            redirect_params = $.param({
                'page': 'embm-settings'
            }),
            redirect_url = url.split('?')[0] + '?' + redirect_params, // Reset URL
            auth_root = 'https://wp.erinmorelli.com/embm/untappd',
            auth_params = $.param({
                'embm-origin': redirect_url
            }),
            auth_url = auth_root + '?' + auth_params;

        window.location = auth_url;
    });
});
