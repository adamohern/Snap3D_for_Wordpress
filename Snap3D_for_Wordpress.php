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




// Create our own duplicate of the Featured Image meta box
function render_new_post_thumbnail_meta_box() {
    global $post_type; // lets call the post type

    // remove the old meta box
    remove_meta_box( 'postimagediv','post','side' );

    // adding the new meta box.
    add_meta_box('postimagediv', __('Featured Image'), 'new_post_thumbnail_meta_box', $post_type, 'side', 'low');
}
add_action('do_meta_boxes', 'render_new_post_thumbnail_meta_box');




// Glom onto our copy of the Featured Image meta box
function new_post_thumbnail_meta_box() {
    global $post; // we know what this does

    $thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true ); // grabing the thumbnail id of the post
    echo _wp_post_thumbnail_html( $thumbnail_id ); // echoing the html markup for the thumbnail

    wp_nonce_field( basename( __FILE__ ), 'Snap3D_nonce' );
    $Snap3D_stored_meta = get_post_meta( $post->ID );

    ?>
    <p>
        <label for="Snap3D-URL" class="Snap3D-row-title"><?php _e( 'Snap3D URL', 'Snap3D-textdomain' )?></label>
        <input type="text" name="Snap3D-URL" id="Snap3D-URL" value="<?php if ( isset ( $Snap3D_stored_meta['Snap3D-URL'] ) ) echo $Snap3D_stored_meta['Snap3D-URL'][0]; ?>" />
    </p>
    <?php
}




// When post saves, save our post meta
function Snap3D_meta_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'Snap3D_nonce' ] ) && wp_verify_nonce( $_POST[ 'Snap3D_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'Snap3D-URL' ] ) ) {
        update_post_meta( $post_id, 'Snap3D-URL', sanitize_text_field( $_POST[ 'Snap3D-URL' ] ) );
    }

}
add_action( 'save_post', 'Snap3D_meta_save' );




// Render thumbnail in theme
add_filter( 'post_thumbnail_html', 'filter_thumb', 10, 2 );
function filter_thumb( $content ) {

    if($snap3d_url = get_post_meta( get_the_ID(), 'Snap3D-URL', true )){

        // Remove trailing slashes
        $snap3d_url = rtrim($snap3d_url, '/');

        if($snap3d_id = extract_id_from_url($snap3d_url)){
            $embed = "<!-- Snap3D_for_Wordpress: embedding post $snap3d_id -->";
        } else {
            $embed = "<!-- Snap3D_for_Wordpress: '$snap3d_url' is invalid. -->";
        }

    } else {
        $embed = "<!-- Snap3D_for_Wordpress: no URL provided. -->";
    }

    return $content . "\n<!-- Snap3D_for_Wordpress was here. -->\n$embed\n";
}


function extract_id_from_url($url){
    //$url='http://domain.com/artist/song/music-videos/song-title/9393903';

    if(preg_match("/\/(\d+)$/",$url,$matches)){
      return $matches[1];
    } else {
      return false;
    }
}


?>
