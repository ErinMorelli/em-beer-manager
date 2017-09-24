/**
 * Copyright (c) 2013-2017, Erin Morelli.
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
    ajaxurl,
    tb_remove,
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
        url_params = {},
        ajax_params = {
            '_nonce': embm_settings.ajax_nonce,
        },
        ajax_response = function (response) {
            if (response && typeof response === 'object' && response.hasOwnProperty('redirect')) {
                window.location = response.redirect;
            } else {
                window.location.reload();
            }
        },
        ajax_error = function (spinner) {
            spinner.removeClass();
            spinner.addClass('dashicons dashicons-warning');
            spinner.prop('title', embm_settings.error);
        },
        spinner = $('<span class="spinner is-active embm-settings--spinner"></span>'),
        untappd_check = $('#embm_untappd_check'),
        settings_nav_hidden = (localStorage.embm_hide_settings_nav === 'true'),
        usage_nav_hidden = (localStorage.embm_hide_usage_nav === 'true'),
        utfb_sections = $('tr.embm-utfb-section'),
        hash,
        page,
        clean_url;

    // Detect Internet Explorer
    function isInternetExplorer() {
        var ua = window.navigator.userAgent,
            msie = ua.indexOf('MSIE ');
        return (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./i));
    }

    // Show/hide Untappd content
    function untappdShowHide(checked) {
        var tables = $('form.embm-settings--form > table.form-table');
        tables.each(function (index, table) {
            var rows = $(table).find('tr');
            if (!rows.length) {
                return;
            }
            if (checked) {
                $(rows[2]).hide();
                if (!index) {
                    $(rows[1]).hide();
                    $(rows[3]).hide();
                    $(rows[4]).hide();
                }
            } else {
                $(rows[2]).show();
                if (!index) {
                    $(rows[1]).show();
                    $(rows[3]).show();
                    $(rows[4]).show();
                }
            }
        });
    }

    // Get URL params
    if (window.location.search) {
        window.location.search.replace(/^\?/, '').split('&').forEach(function (param) {
            var split = param.split('=');
            url_params[split[0]] = split[1];
        });
    }

    // Check for a hash in the URL
    if (url_hash) {
        // Don't jump to div
        window.scrollTo(0, 0);

        // Get hash without the #
        hash = url_hash.slice(1);

        // Add/remove active classes
        if (hash !== '' && $('#embm-settings--tabs').find('.nav-tab-' + hash).length) {
            $('#embm-settings--tabs').find('.nav-tab').removeClass('nav-tab-active');
            $('#embm-settings--tabs').find('.nav-tab-' + hash).addClass('nav-tab-active');
        }
    }

    // Clean URL after page load
    if (url_params.hasOwnProperty('page') && url_params.page === 'embm-settings') {
        // Set vars
        page = url.substring(url.lastIndexOf('/') + 1); // Page URL
        clean_url = page.split('?')[0] + '?page=embm-settings' + url_hash; // Reset URL

        // Update URL
        window.history.replaceState(null, null, clean_url);
    }

    // Show/hide Untappd content on page load
    if (untappd_check) {
        untappdShowHide(untappd_check.is(':checked'));
    }

    // Show/hide settings navigation on page load
    if (settings_nav_hidden) {
        $('.embm-settings--navbox').css('right', '-185px');
        $('#embm-settings--navbox-toggle').removeClass();
        $('#embm-settings--navbox-toggle').addClass('dashicons dashicons-arrow-left-alt2');
    }

    // Show/hide usage navigation on page load
    if (usage_nav_hidden) {
        $('.embm-usage--navbox').css('right', '-185px');
        $('#embm-usage--navbox-toggle').removeClass();
        $('#embm-usage--navbox-toggle').addClass('dashicons dashicons-arrow-left-alt2');
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

    // Dismiss notices
    $('.embm-notice.notice button.notice-dismiss').on('click', function (e) {
        e.preventDefault();

        // Set vars
        var $el = $('.embm-notice.notice');

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

    // Settings page nav panel toggle
    $('#embm-settings--navbox-toggle').on('click', function (e) {
        var icon = $(this),
            hidden = (localStorage.embm_hide_settings_nav === 'true'),
            offset = isInternetExplorer() ? '-170px' : '-185px',
            right = hidden ? '0px' : offset,
            arrow = hidden ? 'right' : 'left';

        $(this).parent().animate({ right: right }, function () {
            localStorage.embm_hide_settings_nav = !hidden;
            icon.removeClass();
            icon.addClass('dashicons dashicons-arrow-' + arrow + '-alt2');
        });
    });

    // Usage page nav panel toggle
    $('#embm-usage--navbox-toggle').on('click', function (e) {
        var icon = $(this),
            hidden = (localStorage.embm_hide_usage_nav === 'true'),
            offset = isInternetExplorer() ? '-130px' : '-145px',
            right = hidden ? '0px' : offset,
            arrow = hidden ? 'right' : 'left';

        $(this).parent().animate({ right: right }, function () {
            localStorage.embm_hide_usage_nav = !hidden;
            icon.removeClass();
            icon.addClass('dashicons dashicons-arrow-' + arrow + '-alt2');
        });
    });

    // Untappd integration checkbox
    $('#embm_untappd_check').on('change', function (e) {
        untappdShowHide(e.target.checked);
    });

    // Activate color picker
    $('#embm_untappd_rating_color').wpColorPicker();

    // Activate opacity slider
    $('#embm-settings--rating-opacity--slider').slider({
        min: 0,
        max: 50,
        step: 1,
        value: parseFloat($('#embm_untappd_rating_opacity').val()),
        slide: function (event, ui) {
            $('#embm_untappd_rating_opacity').val(ui.value);
            $('#embm-settings--rating-opacity--slider .ui-slider-handle').text(ui.value + '%');
        },
        create: function (event, ui) {
            $('#embm-settings--rating-opacity--slider .ui-slider-handle').text(
                $(this).slider('value') + '%'
            );
        }
    });

    // Select icon option
    $('#embm_untappd_icons').val(embm_settings.options.embm_untappd_icons);

    // Toggle icon image on select change
    $('.embm-settings--untappd-select').on('change', function (e) {
        var img_src = embm_settings.plugin_url + 'assets/img/checkin-button-' + this.value + '.png';
        $('.embm-settings--untappd-icon').attr('src', img_src);
    });

    // Styles reset prompt
    $('button.embm-settings--styles-button').on('click', function (e) {
        e.preventDefault();
        $('#embm-styles-reset-prompt--button').click();
    });

    // Styles reset no
    $('#embm-styles-reset-prompt--no').on('click', function (e) {
        e.preventDefault();
        tb_remove();
    });

    // Styles reset yes
    $('#embm-styles-reset-prompt--yes').on('click', function (e) {
        e.preventDefault();
        ajax_params.action = 'embm-styles-reset';
        $.post(ajaxurl, ajax_params, ajax_response);
    });

    // Donation form
    $('#embm-settings-footer--donate-select').on('change', function (e) {
        var new_value = $(this).val(),
            amount_input = $('#embm-settings-footer--donate-amount');

        if (new_value === 'other') {
            // amount_input.val(0);
            amount_input.show();
        } else {
            amount_input.val(new_value);
            amount_input.hide();
        }

        console.log(amount_input.val());
    });

    /* ---- UNTAPPD AUTHORIZATION ---- */

    // Redirect to Untappd to authorize user
    $('button.embm-labs--authorize').on('click', function (e) {
        e.preventDefault();
        ajax_params.action = 'embm-untappd-authorize';
        $.post(ajaxurl, ajax_params, ajax_response);
    });

    // Redirect to reauthorize Untappd user
    $('button.embm-labs--reauthorize').on('click', function (e) {
        e.preventDefault();
        ajax_params.action = 'embm-untappd-reauthorize';
        $.post(ajaxurl, ajax_params, ajax_response);
    });

    // Redirect to deauthorize Untappd user
    $('a.embm-untappd--deauthorize').on('click', function (e) {
        e.preventDefault();
        ajax_params.action = 'embm-untappd-deauthorize';
        $.post(ajaxurl, ajax_params, ajax_response);
    });

    /* ---- UNTAPPD METABOX ---- */

    // Toggle beer selection dropdown
    $('.embm-metabox--untappd-select select').on('change', function (e) {
        var id_input = $('#embm_untappd'),
            is_reset = (this.value === ''),
            new_value = is_reset ? id_input.data('value') : this.value;

        // Set input & readonly
        id_input.attr('readonly', !is_reset);
        id_input.val(new_value);
    });

    // Handle beer cache flush requests
    $('.embm-metabox--untappd-flush a').on('click', function (e) {
        e.preventDefault();

        var api_root = $(this).data('api-root'),
            untappd_id = $('#embm_untappd').val();

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-untappd-flush-beer';
        ajax_params.post_id = url_params.post;
        ajax_params.beer_id = untappd_id;
        ajax_params.api_root = api_root;

        // Make AJAX request
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();

            // Show error for bad response
            if (typeof response === 'string') {
                $(e.target).parent().append(
                    '<span class="dashicons dashicons-warning" title="' + response + '"></span>'
                );
            }
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    // Handle beer sync requests
    $('.embm-metabox--untappd-sync a').on('click', function (e) {
        e.preventDefault();

        // Check for user confirmation
        if (!window.confirm(embm_settings.sync_confirm_single)) {
            return;
        }

        // Get API root
        var api_root = $(this).data('api-root');

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-untappd-sync';
        ajax_params.sync_type = 1;
        ajax_params.post_id = url_params.post;
        ajax_params.api_root = api_root;

        // Make AJAX request
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();

            // Show error for bad response
            if (typeof response === 'string') {
                $(e.target).parent().append(
                    '<span class="dashicons dashicons-warning" title="' + response + '"></span>'
                );
            } else {
                // Reload page
                location.reload();
            }
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    /* ---- UNTAPPD WIDGET ---- */

    // Handle beer cache flush requests
    $('.embm-untappd-widget .embm-untappd-widget--refresh-button a').on('click', function (e) {
        e.preventDefault();

        var api_root = $(this).data('api-root'),
            widget = $(this).closest('.embm-untappd-widget'),
            brewery_id = widget.find('.embm-untappd-widget--brewery input').val();

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-untappd-flush-checkins';
        ajax_params.brewery_id = brewery_id;
        ajax_params.api_root = api_root;

        // Make AJAX request
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();

            // Show error for bad response
            if (response.xml === null || response.api === null) {
                var error = '<p class="notice notice-' + response.error.type + '" style="font-size:12px;background:#fafafa;">';
                error += '<strong>' + response.error.title + '</strong> ' + response.error.message + '</p>';
                widget.append(error);
            }
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    /* ---- LABS / UNTAPPD ---- */

    // Redirect to flush Untappd cache
    $('a.embm-untappd--flush').on('click', function (e) {
        e.preventDefault();

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-untappd-flush';

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    // Handle import requests
    $('a.embm-untappd--import').on('click', function (e) {
        e.preventDefault();

        var import_type = $(this).data('type'),
            api_root = $('#embm-untappd-api-root').val(),
            brewery_id = $('#embm-untappd-brewery-id').val(),
            with_collabs = $('#embm-untappd--import-collabs').is(':checked');

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-untappd-import';
        ajax_params.import_type = import_type;
        ajax_params.api_root = api_root;
        ajax_params.brewery_id = brewery_id;
        ajax_params.with_collabs = with_collabs;
        ajax_params.beer_ids = $('#embm-untappd-beer-ids').val();
        ajax_params.beer_id = $('#embm-untappd-beer-id').val();

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    // Confirm delete missing checkbox
    $('#embm-untappd--sync-delete').on('click', function (e) {
        if ($(this).is(':checked')) {
            return window.confirm(embm_settings.sync_confirm_rm);
        }
    });

    // Handle sync requests
    $('a.embm-untappd--sync').on('click', function (e) {
        e.preventDefault();

        // Check for user confirmation
        if (!window.confirm(embm_settings.sync_confirm_plural)) {
            return;
        }

        var api_root = $('#embm-untappd-api-root').val(),
            delete_missing = $('#embm-untappd--sync-delete').is(':checked');

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-untappd-sync';
        ajax_params.sync_type = 2;
        ajax_params.api_root = api_root;
        ajax_params.delete_missing = delete_missing;

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    /* ---- LABS / UTFB ---- */

    // Connect a UTFB account
    $('a.embm-utfb--connect').on('click', function (e) {
        e.preventDefault();

        // Start spinner
        spinner.insertAfter($(this));

        ajax_params.action = 'embm-utfb-connect';
        ajax_params.api_key = $('#embm-utfb--apikey').val();
        ajax_params.email = $('#embm-utfb--email').val();

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    // Disconnect a UTFB account
    $('a.embm-utfb--disconnect').on('click', function (e) {
        e.preventDefault();
        ajax_params.action = 'embm-utfb-disconnect';
        $.post(ajaxurl, ajax_params, ajax_response);
    });

    // Toggle enable/disable section items
    function toggleUtfbSection(section, disable) {
        var section_select = section.find('select.embm-utfb--dropdown'),
            section_buttons = section.find('button.button');

        // Enable items
        [section_select, section_buttons].forEach(function (item) {
            item.prop('disabled', disable);
            item.prop('title', disable ? embm_settings.utfb_section_notice : null);
            item.css('cursor', disable ? 'not-allowed' : 'pointer');
        });

        // Reset selects
        if (disable) {
            section_select.val('');
        }
    }

    // Load next utfb import dropdown
    function loadUtfbDropdown(dropdown) {
        var resource = $(dropdown).data('action'),
            resource_id = $(dropdown).val();

        // Bail if no resource
        if (!resource) {
            return false;
        }

        // Reset all child sections
        $(dropdown)
            .closest('.embm-utfb-section')
            .nextAll('.embm-utfb-section')
            .each(function (idx, child_section) {
                toggleUtfbSection($(child_section), true);
            });

        // Check for resource ID
        if (!resource_id) {
            return false;
        }

        ajax_params.action = 'embm-utfb-dropdown';
        ajax_params.resource = resource;
        ajax_params.resource_id = resource_id;

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            if (response.error) {
                return;
            }

            // Find objects for resource
            var select = $('#embm-utfb-' + resource + '-id');

            // Remove existing options
            select.children('option').each(function (idx, option) {
                if (idx) {
                    $(option).remove();
                }
            });

            // Populate menus select
            response.items.forEach(function (item) {
                select.append('<option value=' + item.id + '>' + item.name + '</option>');
            });

            // Enable items
            toggleUtfbSection($('tr.embm-utfb-section--' + resource), false);
        });
    }

    // Handle select dropdown changes
    $('select.embm-utfb--dropdown').on('change', function (e) {
        e.preventDefault();
        loadUtfbDropdown(this);

        // If this is the location dropdown, update the sync button
        if (this.id === 'embm-utfb-location-id') {
            var location = $(this),
                sync_button = $('button.embm-utfb--sync');

            sync_button.prop('disabled', !location.val() ? true : false);
            sync_button.css('cursor', !location.val() ? 'not-allowed' : 'pointer');
        }
    });

    // Check for value on page load
    utfb_sections.each(function (idx, section) {
        var select = $(section).find('select.embm-utfb--dropdown'),
            sync_button = $('button.embm-utfb--sync');

        // Disable sync button if no location selected
        if (!idx && !select.val()) {
            sync_button.prop('disabled', true);
            sync_button.css('cursor', 'not-allowed');
        }

        if (select.val() || !idx) {
            // Load the dropdown
            loadUtfbDropdown(select);
        } else {
            // Disable items
            toggleUtfbSection($(section), true);
        }
    });

    // Import UTFB objects
    $('.embm-utfb--import').on('click', function (e) {
        e.preventDefault();

        // Get import data
        var resources = {},
            resource = $(this).data('resource'),
            resource_types = embm_settings.utfb_resources,
            import_all = $(this).parent().hasClass('embm-utfb-section--import-all');

        // Start spinner
        spinner.insertAfter($(this));

        // Get resource IDs
        resource_types.forEach(function (resource_type) {
            resources[resource_type] = $('#embm-utfb-' + resource_type + '-id').val();
        });

        // Set up ajax action
        ajax_params.action = 'embm-utfb-import';
        ajax_params.resource = resource;
        ajax_params.resources = resources;
        ajax_params.import_all = import_all;

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    // Confirm delete missing checkbox
    $('#embm-utfb--sync-delete').on('click', function (e) {
        if ($(this).is(':checked')) {
            return window.confirm(embm_settings.sync_confirm_utfb_rm);
        }
    });

    // Sync UTFB objects
    $('button.embm-utfb--sync').on('click', function (e) {
        e.preventDefault();

        // Check for user confirmation
        if (!window.confirm(embm_settings.sync_confirm_utfb)) {
            return;
        }

        // Get import data
        var resources = {},
            resource_types = ['location', 'menu', 'section', 'beer'],
            delete_missing = $('#embm-utfb--sync-delete').is(':checked');

        // Start spinner
        spinner.insertAfter($(this));

        // Get resource IDs
        resource_types.forEach(function (resource_type) {
            resources[resource_type] = $('#embm-utfb-' + resource_type + '-id').val();
        });

        // Set up ajax action
        ajax_params.action = 'embm-utfb-sync';
        ajax_params.resource = 'menu';
        ajax_params.resources = resources;
        ajax_params.import_all = true;
        ajax_params.delete_missing = delete_missing;

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });

    // Redirect to flush UTFB cache
    $('a.embm-utfb--flush').on('click', function (e) {
        e.preventDefault();

        // Start spinner
        spinner.insertAfter($(this));

        // Set AJAX params
        ajax_params.action = 'embm-utfb-flush';

        // Make AJAX request & reload page
        $.post(ajaxurl, ajax_params, function (response) {
            spinner.remove();
            ajax_response(response);
        }).fail(function () {
            ajax_error(spinner);
        });
    });
});
