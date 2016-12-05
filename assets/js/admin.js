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

    // Get URL and hash
    var url = window.location.href,
        url_hash = window.location.hash,
        hash,
        page,
        clean_url;

    // Check for a hash in the URL
    if (location.hash) {
        // Don't jump to div
        window.scrollTo(0, 0);

        // Get hash without the #
        hash = url_hash.slice(1);

        // Add/remove active classes
        if (hash !== '') {
            $('#embm-settings--tabs').find('.nav-tab').removeClass('nav-tab-active');
            $('#embm-settings--tabs').find('.nav-tab-' + hash).addClass('nav-tab-active');
        }
    }

    // Clean URL after page load
    if (!!window.location.search.substring(1).match(/page=embm-settings/)) {
        // Set vars
        page = url.substring(url.lastIndexOf('/') + 1); // Page URL
        clean_url = page.split('?')[0] + '?page=embm-settings' + url_hash; // Reset URL

        // Update URL
        window.history.replaceState(null, null, clean_url);
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
        window.location.reload();
    });

    // Clean up URL after notice dismissal
    $('.embm-settings--notice.notice button.notice-dismiss').on('click', function (e) {
        e.preventDefault();

        // Set vars
        var $el = $('.embm-settings--notice.notice');

        // Remove notice
        $el.fadeTo(100, 0, function () {
            $el.slideUp(100, function () {
                $el.remove();
            });
        });
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

    // Toggle beer selection dropdown
    $('.embm-untappd--select select').on('change', function (e) {
        var id_input = $('.embm-untappd--id input');

        // Set input readonly
        id_input.attr('readonly', (this.value !== ''));

        // Set input value
        id_input.val(this.value);
    });

    // Redirect to Untappd to authorize user
    $('button.embm-labs--authorize').on('click', function (e) {
        e.preventDefault();
        var redirect_params = $.param({
                'page': 'embm-settings'
            }),
            redirect_url = url.split('?')[0] + '?' + redirect_params, // Reset URL
            auth_params = $.param({
                'embm-origin': redirect_url
            }),
            auth_url = embm_settings.untappd_auth_url + '?' + auth_params;

        window.location = auth_url;
    });

    // Redirect to reauthorize Untappd user
    $('a.embm-untappd--reauthorize').on('click', function (e) {
        e.preventDefault();
        var reauth_params = $.param({
                'page': 'embm-settings',
                'embm-untappd-reauthorize': 1
            }),
            reauth_url = url.split('?')[0] + '?' + reauth_params + url_hash; // Reset URL

        window.location = reauth_url;
    });

    // Redirect to deauthorize Untappd user
    $('a.embm-untappd--deauthorize').on('click', function (e) {
        e.preventDefault();
        var deauth_params = $.param({
                'page': 'embm-settings',
                'embm-untappd-deauthorize': 1
            }),
            deauth_url = url.split('?')[0] + '?' + deauth_params + url_hash; // Reset URL

        window.location = deauth_url;
    });

    // Redirect to flush Untappd cache
    $('a.embm-untappd--flush').on('click', function (e) {
        e.preventDefault();
        var flush_params = $.param({
                'page': 'embm-settings',
                'embm-untappd-flush': 1
            }),
            flush_url = url.split('?')[0] + '?' + flush_params + url_hash; // Reset URL

        window.location = flush_url;
    });
});
