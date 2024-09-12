<?php


// Tambahkan field "Show Thumbnail" ke item menu
function custom_nav_menu_show_thumbnail_checkbox($item_id, $item, $depth, $args, $id) {

    if ( $item->type != 'post_type' ) {
        return;
    }

    // Ambil nilai metadata dari item menu
    $show_thumbnail = get_post_meta($item_id, '_menu_item_show_thumbnail', true);
    ?>
    <p class="field-show-thumbnail description description-wide">
        <label for="edit-menu-item-show-thumbnail-<?php echo $item_id; ?>">
            <input type="checkbox" id="edit-menu-item-show-thumbnail-<?php echo $item_id; ?>" name="menu-item-show-thumbnail[<?php echo $item_id; ?>]" value="1" <?php checked($show_thumbnail, 1); ?> />
            <?php esc_html_e('Show Thumbnail', 'textdomain'); ?>
        </label>
    </p>
    <?php
}
add_action('wp_nav_menu_item_custom_fields', 'custom_nav_menu_show_thumbnail_checkbox', 10, 5);

// Simpan nilai checkbox "Show Thumbnail" di metadata item menu
function custom_update_nav_menu_show_thumbnail($menu_id, $menu_item_db_id) {
    if (isset($_POST['menu-item-show-thumbnail'][$menu_item_db_id])) {
        update_post_meta($menu_item_db_id, '_menu_item_show_thumbnail', 1);
    } else {
        delete_post_meta($menu_item_db_id, '_menu_item_show_thumbnail');
    }
}
add_action('wp_update_nav_menu_item', 'custom_update_nav_menu_show_thumbnail', 10, 2);

// Tambahkan kelas CSS pada item menu yang memiliki thumbnail aktif
function custom_nav_menu_css_class($classes, $item) {
    $show_thumbnail = get_post_meta($item->ID, '_menu_item_show_thumbnail', true);
    if ($show_thumbnail) {
        $classes[] = 'menu-item-show-thumbnail';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'custom_nav_menu_css_class', 10, 2);
