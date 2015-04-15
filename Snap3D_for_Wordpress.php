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

if(current_theme_supports('post-thumbnails')){
    add_filter( 'post_thumbnail_html', 'filter_thumb', 10, 2 );
    function filter_thumb( $content ) {
        return $content . "\n<!-- Snap3D_for_Wordpress was here. -->";
    }
}

?>
