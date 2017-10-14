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
 * @package EMBM\Admin\Tabs\Usage
 */

// Set up Sections
$sections = array(
    array(
        'id'       => 'embm_single_beer_display',
        'title'    => 'Single Beer Display'
    ),
    array(
        'id'       => 'embm_list_all_beers',
        'title'    => 'List All Beers'
    ),
    array(
        'id'       => 'embm_beer_menu_display',
        'title'    => 'Beer Menu Display'
    )
);

?>

<div class="embm-usage--navbox">
    <span
        class="dashicons dashicons-arrow-right-alt2"
        title="<?php _e('Collpase/Expand Panel', EMBM_DOMAIN); ?>"
        id="embm-usage--navbox-toggle">
    </span>
    <ul>
        <li><strong>Jump to:</strong></li>
        <li><a href="#top"><?php _e('Top', EMBM_DOMAIN); ?></a></li>
        <?php foreach ($sections as $section): ?>
            <li>
                <a href="#<?php echo $section['id']; ?>"><?php echo $section['title']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<a name="embm_single_beer_display"></a>
<h2><?php _e('Single Beer Display', EMBM_DOMAIN); ?></h2>

<p><?php _e('These will display a single beer entry given it\'s ID number.', EMBM_DOMAIN); ?></p>

<h3 class="embm-settings--subhead"><?php _e('Shortcode', EMBM_DOMAIN); ?></h3>

<blockquote>
    <code>[beer id="beer id"]</code>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Template tag', EMBM_DOMAIN); ?></h3>

<blockquote>
    <code><?php echo htmlentities('<?php echo EMBM_Output_Shortcodes_Beer_display( $beer_id, $args ); ?>'); ?></code></p>
    <p>
        <?php
            printf(
                __('Where %s is required and %s is a PHP array of comma-separated %s pairs. For example', EMBM_DOMAIN),
                '<code>$beer_id</code>',
                '<code>$args</code>',
                sprintf('<code>%s => %s</code>', __('key', EMBM_DOMAIN), __('value', EMBM_DOMAIN))
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

<h3 class="embm-settings--subhead"><?php _e('Options', EMBM_DOMAIN); ?></h3>

<p><?php _e('For use with both the shortcode and template code.', EMBM_DOMAIN); ?></p>

<table class="embm-settings--table" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th><?php _e('Option Name', EMBM_DOMAIN); ?></th>
            <th><?php _e('Values', EMBM_DOMAIN); ?></th>
            <th><?php _e('Default', EMBM_DOMAIN); ?></th>
            <th><?php _e('Description', EMBM_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><strong>show_profile</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Beer Profile" information', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_extras</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Extra Beer Information" section', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_rating</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd beer rating', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_checkins</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd check-ins section', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>checkins_count</strong></code></td>
            <td><?php _e('A number', EMBM_DOMAIN); ?><br /> e.g. <code>10</code></td>
            <td><code>5</code></td>
            <td><?php printf(
                __('The number of recent Untappd check-ins to display. Limit is %s.', EMBM_DOMAIN),
                '<code>15</code>'
            ); ?></td>
        </tr>
    </tbody>
</table>

<br />

<a name="embm_list_all_beers"></a>
<h2><?php _e('List All Beers', EMBM_DOMAIN); ?></h2>

<p><?php _e('These will display a formatted listing of all beers.', EMBM_DOMAIN); ?></p>

<h3 class="embm-settings--subhead"><?php _e('Shortcode', EMBM_DOMAIN); ?></h3>

<blockquote>
    <code>[beer-list]</code>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Template tag', EMBM_DOMAIN); ?></h3>

<blockquote>
    <code><?php echo htmlentities('<?php echo EMBM_Output_Shortcodes_List_display( $args ); ?>'); ?></code></p>
    <p>
        <?php
            printf(
                __('Where %s is a PHP array of comma-separated %s pairs. For example', EMBM_DOMAIN),
                '<code>$args</code>',
                sprintf('<code>%s => %s</code>', __('key', EMBM_DOMAIN), __('value', EMBM_DOMAIN))
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

<h3 class="embm-settings--subhead"><?php _e('Options', EMBM_DOMAIN); ?></h3>

<p><?php _e('For use with both the shortcode and template code.', EMBM_DOMAIN); ?></p>

<table class="embm-settings--table" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th><?php _e('Option Name', EMBM_DOMAIN); ?></th>
            <th><?php _e('Values', EMBM_DOMAIN); ?></th>
            <th><?php _e('Default', EMBM_DOMAIN); ?></th>
            <th><?php _e('Description', EMBM_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><strong>show_profile</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Beer Profile" information for each listing', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_extras</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the "Extra Beer Information" section for each listing', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_rating</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd beer rating', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>style</strong></code></td>
            <td><?php _e('Comma-separated list of style names, slugs, or IDs', EMBM_DOMAIN); ?><br />e.g. <code>"india-pale-ale, pale-ale"</code></td>
            <td>n/a</td>
            <td><?php _e('Displays only beers belonging to specific beer styles', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>group</strong></code></td>
            <td><?php _e('Comma-separated list of group names, slugs, or IDs', EMBM_DOMAIN); ?><br />e.g. <code>"Seasonal, Barrel-Aged"</code></td>
            <td>n/a</td>
            <td><?php _e('Displays only beers belonging to specific groups', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>exclude</strong></code></td>
            <td><?php _e('Comma-separated list of beer IDs', EMBM_DOMAIN); ?><br />e.g. <code>"4,23,24"</code></td>
            <td>n/a</td>
            <td><?php _e('Hides listed beers from output', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>beers_per_page</strong></code></td>
            <td><?php _e('A number', EMBM_DOMAIN); ?><br /> e.g. <code>5</code></td>
            <td><code>-1</code><br /><?php _e('Shows all beers', EMBM_DOMAIN); ?></td>
            <td><?php _e('Paginates output and displays the given number of beers per page', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>offset</strong></code></td>
            <td><?php _e('A number', EMBM_DOMAIN); ?><br /> e.g. <code>2</code></td>
            <td><code>0</code><br /><?php _e('Starts at the first beer', EMBM_DOMAIN); ?></td>
            <td><?php _e('Offsets the output of beers by given number', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>paginate</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Disables/enables pagination', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>orderby</strong></code></td>
            <td>
                <?php
                    printf(
                        __('See %s for options', EMBM_DOMAIN),
                        sprintf(
                            '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">%s</a>',
                            __('this list', EMBM_DOMAIN)
                        )
                    );
                ?>
            </td>
            <td><code>"date"</code></td>
            <td><?php _e('Orders beer list output by the given parameter', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>order</strong></code></td>
            <td><code>ASC, DSC</code></td>
            <td><code>DSC</code></td>
            <td>
                <?php
                    printf(
                        __('Sorts beer list by %s value in ascending or descending order', EMBM_DOMAIN),
                        '<code>orderby</code>'
                    );
                ?>
            </td>
        </tr>
    </tbody>
</table>

<br />

<a name="embm_beer_menu_display"></a>
<h2><?php _e('Beer Menu Display', EMBM_DOMAIN); ?></h2>

<p><?php _e('These will display a beer menu given it\'s Name, Slug, or ID number.', EMBM_DOMAIN); ?></p>

<h3 class="embm-settings--subhead"><?php _e('Shortcode', EMBM_DOMAIN); ?></h3>

<blockquote>
    <code>[beer-menu menu="menu id"]</code>
</blockquote>

<h3 class="embm-settings--subhead"><?php _e('Template tag', EMBM_DOMAIN); ?></h3>

<blockquote>
    <code><?php echo htmlentities('<?php echo EMBM_Output_Shortcodes_Menu_display( $menu_id, $args ); ?>'); ?></code></p>
    <p>
        <?php
            printf(
                __('Where %s is required and %s is a PHP array of comma-separated %s pairs. For example', EMBM_DOMAIN),
                '<code>$menu_id</code>',
                '<code>$args</code>',
                sprintf('<code>%s => %s</code>', __('key', EMBM_DOMAIN), __('value', EMBM_DOMAIN))
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

<h3 class="embm-settings--subhead"><?php _e('Options', EMBM_DOMAIN); ?></h3>

<p><?php _e('For use with both the shortcode and template code.', EMBM_DOMAIN); ?></p>

<table class="embm-settings--table" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <th><?php _e('Option Name', EMBM_DOMAIN); ?></th>
            <th><?php _e('Values', EMBM_DOMAIN); ?></th>
            <th><?php _e('Default', EMBM_DOMAIN); ?></th>
            <th><?php _e('Description', EMBM_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><strong>show_rating</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the Untappd beer rating', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_last_updated</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the menu\'s last updated timestamp', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_thumbnail</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the beer featured image thumbnails', EMBM_DOMAIN); ?></td>
        </tr>
        <tr>
            <td><code><strong>show_description</strong></code></td>
            <td><code>true, false</code></td>
            <td><code>true</code></td>
            <td><?php _e('Displays or hides the menu section descriptions', EMBM_DOMAIN); ?></td>
        </tr>
    </tbody>
</table>
