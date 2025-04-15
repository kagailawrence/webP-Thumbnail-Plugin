<?php
/**
 * Plugin Name: AWT WebP Thumbnail Assigner(Dazzcode)
 * Description: Automatically assigns WebP images as thumbnails for WooCommerce products using smart fuzzy matching.
 * Version: 1.0
 * Author: Lawrence Maina Kagai(Dazzcode)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('admin_menu', function () {
    add_submenu_page('tools.php', 'Assign WebP Thumbnails', 'WebP Thumbnails', 'manage_options', 'assign-webp-thumbnails', function () {
        echo '<div class="wrap"><h1>Assign WebP Thumbnails</h1>';
        awt_assign_webp_thumbnails();
        echo '</div>';
    });
});

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

function awt_assign_webp_thumbnails() {
    $upload_dir = wp_upload_dir();
    $webp_folder = trailingslashit($upload_dir['basedir']) . 'webp/';
    $products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);
    $count = 0;

    foreach ($products as $product_post) {
        $product_id = $product_post->ID;
        $title_slug = sanitize_title($product_post->post_title);
        $post_slug = $product_post->post_name;

        $filenames_to_try = [$title_slug . '.webp', $post_slug . '.webp'];
        $image_found = false;

        // 1. Try exact match in media library
        foreach ($filenames_to_try as $filename) {
            $media = get_posts([
                'post_type' => 'attachment',
                'posts_per_page' => 1,
                'meta_query' => [[
                    'key' => '_wp_attached_file',
                    'value' => $filename,
                    'compare' => 'LIKE',
                ]]
            ]);

            if (!empty($media)) {
                set_post_thumbnail($product_id, $media[0]->ID);
                echo "<p>✅ Set <strong>$filename</strong> from media library for <em>{$product_post->post_title}</em></p>";
                $image_found = true;
                $count++;
                break;
            }
        }

        // 2. Try fuzzy match in media library
        if (!$image_found) {
            $fuzzy_media = get_posts([
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_mime_type' => 'image/webp',
            ]);

            foreach ($fuzzy_media as $media_item) {
                $meta_path = get_post_meta($media_item->ID, '_wp_attached_file', true);
                if (strpos($meta_path, $title_slug) !== false) {
                    set_post_thumbnail($product_id, $media_item->ID);
                    echo "<p>🔍 Fuzzy matched and set <strong>$meta_path</strong> for <em>{$product_post->post_title}</em></p>";
                    $image_found = true;
                    $count++;
                    break;
                }
            }
        }

        // 3. Try upload from /uploads/webp/ folder
        if (!$image_found) {
            foreach ($filenames_to_try as $filename) {
                $file_path = $webp_folder . $filename;
                if (file_exists($file_path)) {
                    $uploaded_file = [
                        'name'     => $filename,
                        'type'     => 'image/webp',
                        'tmp_name' => $file_path,
                        'error'    => 0,
                        'size'     => filesize($file_path),
                    ];

                    $attach_id = media_handle_sideload($uploaded_file, $product_id);

                    if (!is_wp_error($attach_id)) {
                        set_post_thumbnail($product_id, $attach_id);
                        echo "<p>📤 Uploaded and set <strong>$filename</strong> from file system for <em>{$product_post->post_title}</em></p>";
                        $image_found = true;
                        $count++;
                        break;
                    } else {
                        echo "<p>❌ Error uploading $filename: " . $attach_id->get_error_message() . "</p>";
                    }
                }
            }
        }

        if (!$image_found) {
            echo "<p>⚠️ No image found for <em>{$product_post->post_title}</em></p>";
        }
    }

    echo "<hr><p><strong>✅ Finished: Updated $count products.</strong></p>";
}
