<?php
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
 * @package EMBM\Admin\Footer
 */

// Get official plugin data
$embm_data = get_plugin_data(EMBM_PLUGIN_DIR.'em-beer-manager.php', false, true);

// Enable thickbox
add_thickbox();

?>
<br /><hr />

<div class="embm-settings-footer--donate">
    <p class="embm-settings-footer--donate-text">
        <span><?php _e('Like this plugin?', 'embm'); ?></span>
        <a
            class="embm-settings-footer--donate-button thickbox"
            href="#TB_inline?width=250&height=150&inlineId=embm-settings-footer--donate-modal"
            title="<?php _e('Buy me a beer!', 'embm'); ?>"
            target="_blank"
        >
            <?php _e('Buy me a beer!', 'embm'); ?>
        </a>
        <span class="dashicons dashicons-smiley"></span>
    </p>
</div>

<div id="embm-settings-footer--donate-modal" style="display:none;">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" id="embm-settings-footer--donate-form">
        <input type="hidden" name="business" value="98VZ8XS4D4VBY">
        <input type="hidden" name="cmd" value="_donations">
        <input type="hidden" name="item_name" value="Buy Me a Beer!">
        <input type="hidden" name="item_number" value="EM Beer Manager">
        <input type="hidden" name="currency_code" value="USD">
        <input type="hidden" name="return" value="https://www.erinmorelli.com/thanks-paypal">
        <p>
            <select name="amount-select" id="embm-settings-footer--donate-select">
                <option value="6.00" selected><?php printf('%s ($6)', __('A Pint', 'embm')); ?></option>
                <option value="12.00"><?php printf('%s ($12)', __('A 6-Pack', 'embm')); ?></option>
                <option value="24.00"><?php printf('%s ($24)', __('A Case', 'embm')); ?></option>
                <option value="other"><?php _e('Custom Amount', 'embm'); ?></option>
            </select>
            <input
                type="number"
                name="amount"
                step="0.01"
                min="1.00"
                value="6.00"
                style="display:none;"
                id="embm-settings-footer--donate-amount"
            >
            <span class="description"><?php _e('USD', EMBM); ?></span>
        </p>
        <p style="margin-top:5px;">
            <input
                type="submit"
                name="submit"
                class="embm-settings-footer--donate-button"
                value="<?php _e('Buy me a beer!', 'embm'); ?>"
            ><br />
            <img src="<?php echo EMBM_PLUGIN_URL; ?>/assets/img/donate.png" alt="<?php _e('PayPal', 'embm'); ?>" border="0" />
        </p>
    </form>
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
