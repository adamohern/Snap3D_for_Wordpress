<?php
/**
 * Plugin Name: Snap3D_for_Wordpress
 * Plugin URI: https://github.com/adamohern/Snap3D_for_Wordpress
 * Description: A Wordpress plugin for integrating Snap3D.io embeds as featured images or inline content.
 * Version: 150414.3
 * Author: Snap3D
 * Author URI: http://Snap3D.io
 * Network: true
 * License: A short license name. Example: GPU2
 */

/*  Copyright 2015  EvD Media LLC  (email : admin@evd1.tv)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter( 'post_thumbnail_html', 'filter_thumb', 10, 2 );
function filter_thumb( $content ) {
    return $content . "\n<!-- Snap3D_for_Wordpress was here. -->";
}

function new_post_thumbnail_meta_box() {
    global $post; // we know what this does

    echo '&lt;p&gt;Content above the image.&lt;/p&gt;';

    $thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true ); // grabing the thumbnail id of the post
    echo _wp_post_thumbnail_html( $thumbnail_id ); // echoing the html markup for the thumbnail

    echo '&lt;p&gt;Content below the image.&lt;/p&gt;';
}
function render_new_post_thumbnail_meta_box() {
    global $post_type; // lets call the post type

    // remove the old meta box
    remove_meta_box( 'postimagediv','post','side' );

    // adding the new meta box.
    add_meta_box('postimagediv', __('Featured Image'), 'new_post_thumbnail_meta_box', $post_type, 'side', 'low');
}
add_action('do_meta_boxes', 'render_new_post_thumbnail_meta_box');

?>
