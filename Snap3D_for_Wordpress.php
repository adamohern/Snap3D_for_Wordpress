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
    // we know what this does
    global $post;

    // grabing the thumbnail id of the post
    $thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
    // echoing the html markup for the thumbnail
    echo _wp_post_thumbnail_html( $thumbnail_id );

    // create our own nonce for security purposes
    wp_nonce_field( basename( __FILE__ ), 'Snap3D_nonce' );
    // grab existing meta data
    $Snap3D_stored_meta = get_post_meta( $post->ID,'Snap3D-URL',true );

    // render our HTML form ?>
    <p>
        <label for="Snap3D-URL" class="Snap3D-row-title"><?php _e( 'Snap3D URL', 'Snap3D-textdomain' )?></label>
        <input type="text" name="Snap3D-URL" id="Snap3D-URL" value="<?php if ( isset ( $Snap3D_stored_meta ) ) echo $Snap3D_stored_meta; ?>" />
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
function filter_thumb( $content ) {

    // Is there an existing meta field?
    if($snap3d_url = get_post_meta( get_the_ID(), 'Snap3D-URL', true ) && get_post_type()!='lightning_posttype'){

        // Are we able to extract digits from the end?
        if($snap3d_id = extract_id_from_url($snap3d_url)){

            return render_embed($snap3d_id);

        } else {
            $embed = "<!-- Snap3D_for_Wordpress: '$snap3d_url' is invalid. Using default Featured Image. -->\n";
        }

    } else {
        $embed = "<!-- Snap3D_for_Wordpress: no URL provided. Using default Featured Image. -->\n";
    }

    return $embed.$content;


}
add_filter( 'post_thumbnail_html', 'filter_thumb', 10, 2 );




function filter_the_content($content){
    $new_content = preg_replace_callback(
        // Find anything that:
        // - (?i) is case-insensitive
        // - (?<!"|"http:\/\/) Doesn't start with quotes (to avoid urls wrapped in img and a tags)
        // - (http:\/\/)* May or may not start with http://
        // - snap3d.io\/ Definitely containts "snap3d.io/"
        // - [a-z0-9]*\/[a-z0-9\/]* e.g. "3d/123"
        '#(?i)(?<!"|"http:\/\/)(http:\/\/)*snap3d.io\/[a-z0-9]*\/[a-z0-9\/]*#',
        function($matches){
            $id = extract_id_from_url($matches[0]);
            return render_embed($id)."\n<!-- Replacing '$match' using id='$id'. -->\n";
        },
        $content);

    return $new_content;
}
add_filter( 'the_content', 'filter_the_content' );




// Gets iFrame embed code from the Snap3D mothership.
function render_embed($id){

    // Since we use this code internally, we can take a shortcut
    // to the embed code if the correct object class exists.
    // Otherwise we request it from
    // Snap3D.io using file_get_contents().

    if(class_exists('lightning_post_obj')){
        $lightning_post_obj = new lightning_post_obj;
        $lightning_post_obj->set_post_id($id);
        return $lightning_post_obj->get_embed_code();

    } else {

        file_get_contents("http://snap3d.io/?code=$id");

    }

}



// Pulls final digits from url,
// or just uses digits if that's all there is.
function extract_id_from_url($url){

    // Remove trailing slashes
    $url = rtrim($url, '/');

    // Look for digits at the end of the string
    if(preg_match("/(\d+)$/",$url,$matches)){
      return $matches[1];
    } else {
      return false;
    }
}


?>
