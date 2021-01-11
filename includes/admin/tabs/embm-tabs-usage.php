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
 * @package EMBM\Admin\Tabs\Usage
 */

// Set up Sections
$sections = array(
    array(
        'id'       => 'embm_single_beer_display',
        'title'    => __('Single Beer Display', 'em-beer-manager')
    ),
    array(
        'id'       => 'embm_list_all_beers',
        'title'    => __('List All Beers', 'em-beer-manager')
    ),
    array(
        'id'       => 'embm_beer_menu_display',
        'title'    => __('Beer Menu Display', 'em-beer-manager')
    )
);

?>

<div class="embm-usage--navbox">
    <span
        class="dashicons dashicons-arrow-right-alt2"
        title="<?php _e('Collpase/Expand Panel', 'em-beer-manager'); ?>"
        id="embm-usage--navbox-toggle">
    </span>
    <ul>
        <li><strong>Jump to:</strong></li>
        <li><a href="#top"><?php _e('Top', 'em-beer-manager'); ?></a></li>
        <?php foreach ($sections as $section): ?>
            <li>
                <a href="#<?php echo $section['id']; ?>"><?php echo $section['title']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<a name="embm_single_beer_display"></a>
<h2><?php _e('Single Beer Display', 'em-beer-manager'); ?></h2>

<p><?php _e('These will display a single beer entry given it\'s ID number.', 'em-beer-manager'); ?></p>

<h3 class="embm-settings--subhead"><?php _e('Shortcode', 'em-beer-manager'); ?></h3>

<blockquote>
    <code>[beer id="beer id"]</code>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Template tag', 'em-beer-manager'); ?></h3>

<blockquote>
    <code><?php echo htmlentities('<?php echo EMBM_Output_Shortcodes_Beer_display( $beer_id, $args ); ?>'); ?></code></p>
    <p>
        <?php
            printf(
                __('Where %s is required and %s is a PHP array of comma-separated %s pairs. For example', 'em-beer-manager'),
                '<code>$beer_id</code>',
                '<code>$args</code>',
                sprintf('<code>%s => %s</code>', __('key', 'em-beer-manager'), __('value', 'em-beer-manager'))
            );
        ?>:
    </p>
    <p>
        <pre class="embm-settings--code">
<?php echo htmlentities(
    "<?php echo EMBM_Output_Shortcodes_Beer_display( 123, array(\n".
    "    'show_profile'     => false,\n".
    "    'show_extras'      => true,\n".
    "    'show_rating'      => false,\n".
    "    'show_checkins'    => true,\n".
    "    'checkins_count'   => 10\n".
    ") ); ?>"
); ?>
        </pre>
    </p>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Options', 'em-beer-manager'); ?></h3>

<p><?php _e('For use with both the shortcode and template code.', 'em-beer-manager'); ?></p>

<table class="embm-settings--table" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th><?php _e('Option Name', 'em-beer-manager'); ?></th>
            <th><?php _e('Values', 'em-beer-manager'); ?></th>
            <th><?php _e('Default', 'em-beer-manager'); ?></th>
            <th><?php _e('Description', 'em-beer-manager'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><strong>show_profile</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Beer Profile" information', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_extras</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Extra Beer Information" section', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_rating</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd beer rating', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_checkins</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd check-ins section', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>checkins_count</strong></code></td>
            <td><?php _e('A number', 'em-beer-manager'); ?><br /> e.g. <code>10</code></td>
            <td><code>5</code></td>
            <td><?php printf(
                __('The number of recent Untappd check-ins to display. Limit is %s.', 'em-beer-manager'),
                '<code>15</code>'
            ); ?></td>
        </tr>
    </tbody>
</table>

<br />

<a name="embm_list_all_beers"></a>
<h2><?php _e('List All Beers', 'em-beer-manager'); ?></h2>

<p><?php _e('These will display a formatted listing of all beers.', 'em-beer-manager'); ?></p>

<h3 class="embm-settings--subhead"><?php _e('Shortcode', 'em-beer-manager'); ?></h3>

<blockquote>
    <code>[beer-list]</code>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Template tag', 'em-beer-manager'); ?></h3>

<blockquote>
    <code><?php echo htmlentities('<?php echo EMBM_Output_Shortcodes_List_display( $args ); ?>'); ?></code></p>
    <p>
        <?php
            printf(
                __('Where %s is a PHP array of comma-separated %s pairs. For example', 'em-beer-manager'),
                '<code>$args</code>',
                sprintf('<code>%s => %s</code>', __('key', 'em-beer-manager'), __('value', 'em-beer-manager'))
            );
        ?>:
    </p>
    <p>
        <pre class="embm-settings--code">
<?php echo htmlentities(
    "<?php echo EMBM_Output_Shortcodes_List_display( array(\n".
    "    'show_extras'       => false,\n".
    "    'show_rating'       => true,\n".
    "    'beers_per_page'    => 3,\n".
    "    'orderby'           => 'name',\n".
    "    'order'             => 'ASC'\n".
    ") ); ?>"
); ?>
        </pre>
    </p>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Options', 'em-beer-manager'); ?></h3>

<p><?php _e('For use with both the shortcode and template code.', 'em-beer-manager'); ?></p>

<table class="embm-settings--table" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th><?php _e('Option Name', 'em-beer-manager'); ?></th>
            <th><?php _e('Values', 'em-beer-manager'); ?></th>
            <th><?php _e('Default', 'em-beer-manager'); ?></th>
            <th><?php _e('Description', 'em-beer-manager'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><strong>show_profile</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Beer Profile" information for each listing', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_extras</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Extra Beer Information" section for each listing', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_rating</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd beer rating', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>style</strong></code></td>
            <td><?php _e('Comma-separated list of style names, slugs, or IDs', 'em-beer-manager'); ?><br />e.g. <code>"india-pale-ale, pale-ale"</code></td>
            <td>n/a</td>
            <td><?php _e('Displays only beers belonging to specific beer styles', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>group</strong></code></td>
            <td><?php _e('Comma-separated list of group names, slugs, or IDs', 'em-beer-manager'); ?><br />e.g. <code>"Seasonal, Barrel-Aged"</code></td>
            <td>n/a</td>
            <td><?php _e('Displays only beers belonging to specific groups', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>exclude</strong></code></td>
            <td><?php _e('Comma-separated list of beer IDs', 'em-beer-manager'); ?><br />e.g. <code>"4,23,24"</code></td>
            <td>n/a</td>
            <td><?php _e('Hides listed beers from output', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>beers_per_page</strong></code></td>
            <td><?php _e('A number', 'em-beer-manager'); ?><br /> e.g. <code>5</code></td>
            <td><code>-1</code><br /><?php _e('Shows all beers', 'em-beer-manager'); ?></td>
            <td><?php _e('Paginates output and displays the given number of beers per page', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>offset</strong></code></td>
            <td><?php _e('A number', 'em-beer-manager'); ?><br /> e.g. <code>2</code></td>
            <td><code>0</code><br /><?php _e('Starts at the first beer', 'em-beer-manager'); ?></td>
            <td><?php _e('Offsets the output of beers by given number', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>paginate</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Disables/enables pagination', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>orderby</strong></code></td>
            <td>
                <?php
                    printf(
                        __('See %s for options', 'em-beer-manager'),
                        sprintf(
                            '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">%s</a>',
                            __('this list', 'em-beer-manager')
                        )
                    );
                ?>
            </td>
            <td><code>"date"</code></td>
            <td><?php _e('Orders beer list output by the given parameter', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>order</strong></code></td>
            <td><code>ASC, DSC</code></td>
            <td><code>DSC</code></td>
            <td>
                <?php
                    printf(
                        __('Sorts beer list by %s value in ascending or descending order', 'em-beer-manager'),
                        '<code>orderby</code>'
                    );
                ?>
            </td>
        </tr>
    </tbody>
</table>

<br />

<a name="embm_beer_menu_display"></a>
<h2><?php _e('Beer Menu Display', 'em-beer-manager'); ?></h2>

<p><?php _e('These will display a beer menu given it\'s Name, Slug, or ID number.', 'em-beer-manager'); ?></p>

<h3 class="embm-settings--subhead"><?php _e('Shortcode', 'em-beer-manager'); ?></h3>

<blockquote>
    <code>[beer-menu menu="menu id"]</code>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Template tag', 'em-beer-manager'); ?></h3>

<blockquote>
    <code><?php echo htmlentities('<?php echo EMBM_Output_Shortcodes_Menu_display( $menu_id, $args ); ?>'); ?></code></p>
    <p>
        <?php
            printf(
                __('Where %s is required and %s is a PHP array of comma-separated %s pairs. For example', 'em-beer-manager'),
                '<code>$menu_id</code>',
                '<code>$args</code>',
                sprintf('<code>%s => %s</code>', __('key', 'em-beer-manager'), __('value', 'em-beer-manager'))
            );
        ?>:
    </p>
    <p>
        <pre class="embm-settings--code">
<?php echo htmlentities(
    "<?php echo EMBM_Output_Shortcodes_Menu_display(\n".
    "    'Taproom Menu',\n".
    "    array(\n".
    "        'show_rating'       => false,\n".
    "        'show_last_updated' => true,\n".
    "        'show_thumbnail'    => true,\n".
    "        'show_description ' => false,\n".
    "    )\n".
    "); ?>"
); ?>
        </pre>
    </p>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Options', 'em-beer-manager'); ?></h3>

<p><?php _e('For use with both the shortcode and template code.', 'em-beer-manager'); ?></p>

<table class="embm-settings--table" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th><?php _e('Option Name', 'em-beer-manager'); ?></th>
            <th><?php _e('Values', 'em-beer-manager'); ?></th>
            <th><?php _e('Default', 'em-beer-manager'); ?></th>
            <th><?php _e('Description', 'em-beer-manager'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><strong>show_rating</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd beer rating', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_last_updated</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the menu\'s last updated timestamp', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_thumbnail</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the beer featured image thumbnails', 'em-beer-manager'); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_description</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the menu section descriptions', 'em-beer-manager'); ?></td>
        </tr>
    </tbody>
</table>
