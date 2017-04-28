<?php
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
 * @package EMBM\Admin\Footer
 */

// Get official plugin data
$embm_data = get_plugin_data(EMBM_PLUGIN_DIR.'em-beer-manager.php', false, true);

?>

<br /><hr />

<div class="embm-settings-footer--donate">
    <p class="embm-settings-footer--donate-text">
        <?php _e('If you like this plugin, please consider donating to support future development. Thank you!', 'embm'); ?>
    </p>
    <p>
        <a
            href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=98VZ8XS4D4VBY&amp;lc=US&amp;item_name=EM%20Beer%20Manager&amp;item_number=embm%2dplugin%2dsupport&amp;currency_code=USD&amp;bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted&amp;return=http%3A%2F%2Fwww.erinmorelli.com%2Fthanks-paypal%2F"
            title="<?php _e('Donate', 'embm'); ?>"
            target="_blank"
        >
            <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="<?php _e('Donate', 'embm'); ?>" border="0" />
        </a>
    </p>
</div>

<hr />

<div class="embm-settings-footer--credits">
    <!-- Logo graphics and data from Untappd -->
    <p class="embm-settings-footer--credits-untappd">
        <a href="https://untappd.com" target="_blank" rel="nofollow">
            <img src="<?php echo EMBM_PLUGIN_URL; ?>/assets/img/ut-credit.png" alt="<?php _e('Powered by Untappd', 'embm'); ?>" border="0" />
        </a>
    </p>
    <p><?php printf(__('All Untappd logo graphics are the sole property of %s', 'embm'), '<strong>Untappd LLC.</strong>'); ?></p>
    <!-- Admin menu beer icon from SimpleIcon.com-->
    <p>
        <?php printf(
            __('Free beer icon from %s', 'embm'),
            '<a href="http://simpleicon.com" target="_blank" title="simple icon" rel="nofollow"><strong>simple icon</strong></a>'
        ); ?>.
    </p>
    <!-- Everything else -->
    <p>
        <strong><em><?php echo $embm_data['Name']; ?></em></strong> &copy; 2013-<?php echo date('Y'); ?>&nbsp;
        <a href="<?php echo $embm_data['AuthorURI']; ?>" title="<?php echo $embm_data['Author']; ?>" target="_blank">
            <strong><?php echo $embm_data['Author']; ?></strong>
        </a>.
    </p>
</div>
