<?php
/**
 * Copyright (c) 2013-2021, Erin Morelli.
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
 * @package EMBM\Admin\Notices
 */

$SUCCESS_TITLE = __('Success!', 'em-beer-manager');
$ERROR_TITLE   = __('ERROR', 'em-beer-manager') . ':';
$WARNING_TITLE = __('WARNING', 'em-beer-manager') . ':';

// Map of notices
$GLOBALS['EMBM_NOTICE_MAP'] = array(
    'styles-reset' => array(
        '1' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beer styles have been restored.', 'em-beer-manager')
        )
    ),
    'import-success' => array(
        '1' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beer has been imported from Untappd.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beers have been imported from Untappd.', 'em-beer-manager')
        )
    ),
    'import-error' => array(
        '1' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem! You may have reached your API token\'s rate limit for the hour. Please try again later.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem during the import! The beer you specified was not found on Untappd.', 'em-beer-manager')
        ),
        '3' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('This beer does not belong to your brewery! You can only import beers that are owned by your Untappd brewery account.', 'em-beer-manager')
        ),
        '4' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem during the import! Please try again later.', 'em-beer-manager')
        ),
        '5' => array(
            'type'      => 'warning',
            'title'     => $WARNING_TITLE,
            'message'   => __('There was a problem during the import! One or more beers was not imported. Please try again later.', 'em-beer-manager')
        )
    ),
    'sync-success' => array(
        '1' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beers have been updated from Untappd.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beer has been updated from Untappd.', 'em-beer-manager')
        )
    ),
    'sync-error' => array(
        '1' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem during the sync! Please try again later.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'warning',
            'title'     => $WARNING_TITLE,
            'message'   => __('There was a problem during the sync! One or more beers were not updated. Please try again later.', 'em-beer-manager')
        ),
        '3' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem during the sync! The selected beer does not have an Untappd ID.', 'em-beer-manager')
        ),
    ),
    'save-error' => array(
        '1' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem saving your beer\'s Untappd data!', 'em-beer-manager').' '.
                __('You may have reached your API token\'s rate limit for the hour. Please try again later.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem saving your menu\'s Untappd for Business data!', 'em-beer-manager').' '.
                __('You may have reached your API token\'s rate limit for the hour. Please try again later.', 'em-beer-manager')
        )
    ),
    'widget-error' => array(
        '1' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem retrieving check-in data from Untappd!', 'em-beer-manager').' '.
                __('Please try again later.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem refreshing check-in data from Untappd!', 'em-beer-manager').' '.
                __('Please try again later.', 'em-beer-manager')
        )
    ),
    'utfb-error' => array(
        '1' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem connecting your Untappd for Business account! Please try again later.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'error',
            'title'     => $ERROR_TITLE,
            'message'   => __('There was a problem during the import! Please try again later.', 'em-beer-manager')
        ),
        '3' => array(
            'type'      => 'warning',
            'title'     => $WARNING_TITLE,
            'message'   => __('There was a problem during the import! One or more beers were not imported. Please try again later.', 'em-beer-manager')
        ),
        '4' => array(
            'type'      => 'warning',
            'title'     => $WARNING_TITLE,
            'message'   => __('There was a problem during the sync! One or more beers were not updated. Please try again later.', 'em-beer-manager')
        )
    ),
    'utfb-success' => array(
        '1' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your Untappd for Business account has been connected.', 'em-beer-manager')
        ),
        '2' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beers have been imported from your Untappd for Business account.', 'em-beer-manager')
        ),
        '3' => array(
            'type'      => 'updated',
            'title'     => $SUCCESS_TITLE,
            'message'   => __('Your beers have been synced with your Untappd for Business menus.', 'em-beer-manager')
        )
    )
);

/**
 * Displays admin notices based on GET params
 *
 * @return void
 */
function EMBM_Admin_Notices_show()
{
    $notice_map = $GLOBALS['EMBM_NOTICE_MAP'];

    // Keep track of notices to show
    $notices = array();

    // Look for notices in GET request
    foreach ($_GET as $notice_name => $notice_type) {
        // Look for any notices
        preg_match('/^embm-([a-z\-]+)$/', $notice_name, $notice_match);

        // Add notice to the list
        if ($notice_match && array_key_exists($notice_match[1], $notice_map)) {
            $notice_type_map = $notice_map[$notice_match[1]];
            if (array_key_exists($notice_type, $notice_type_map)) {
                array_push($notices, $notice_type_map[$notice_type]);
            }
        }
    }

    // Show notices
    foreach ($notices as $notice) :
    ?>
        <div class="<?php echo $notice['type']; ?> notice embm-notice">
            <p>
                <span class="embm-notice--title"><?php echo $notice['title']; ?></span>
                <span class="embm-notice--message"><?php echo $notice['message'];?></span>
            </p>
            <button type="button" class="notice-dismiss"></button>
        </div>
    <?php
    endforeach;
}

/**
 * Displays a notice about API rate-limit
 *
 * @param string $msg Message to display. Default = null
 *
 * @return void
 */
function EMBM_Admin_Notices_ratelimit($msg = null)
{
    // Set fallback message
    if (is_null($msg)) {
        $msg = __('There was a problem! You may have reached your API token\'s rate limit for the hour. Please try again later.', 'em-beer-manager');
    }

    // Display notice
    echo '<div class="notice notice-warning inline rl-warning"><p>' . $msg;
    echo '<a data-help="embm-untappd-api-ratelimit" class="embm-settings--help">?</a></p></div>';
}
