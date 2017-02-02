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
 * @package EMBM\Admin\Tabs\Labs
 */

// Problem email subject
$email_subj = '[EM Beer Manager] Issue Report';

?>

<p><span class="warning"><?php _e('WARNING', 'embm'); ?>:</span> <?php printf(
    '%s. <span class="emphasis">%s</span>.',
    __('The features on this page are experimental', 'embm'),
    __('Use at your own risk', 'embm')
); ?></p>

<p>
    <?php _e('If you encounter any problems when using these features, please report them to', 'embm');?>
    <a href="mailto:labs@wp.erinmorelli.com?Subject=<?php echo $email_subj; ?>">labs@wp.erinmorelli.com</a>.
</p>

<hr />

<?php

// Load Untappd labs page
if (!EMBM_Core_Beer_disabled()) {
    echo '<h2>'.__('Import from Untappd', 'embm').'</h2>';
    include_once EMBM_PLUGIN_DIR.'includes/admin/labs/embm-labs-untappd.php';
} else {
    echo '<h2>'.__('No Labs features are available right now!', 'embm').'</h2>';
}
