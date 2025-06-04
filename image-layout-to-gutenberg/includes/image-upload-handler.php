<?php
// includes/image-upload-handler.php

if ( ! defined( "WPINC" ) ) {
    die;
}

/**
 * Handles the AJAX request for uploading an image layout.
 */
function ilg_handle_image_layout_upload() {
    // Verify nonce
    check_ajax_referer( "image_layout_upload_nonce", "_ajax_nonce" );

    // Check user capabilities
    if ( ! current_user_can( "upload_files" ) ) {
        wp_send_json_error( __( "You do not have permission to upload files.", "image-layout-to-gutenberg" ), 403 );
    }

    if ( empty( $_FILES["image_layout_file"] ) ) {
        wp_send_json_error( __( "No file was uploaded.", "image-layout-to-gutenberg" ), 400 );
    }

    $file = $_FILES["image_layout_file"];

    // WordPress upload directory
    $upload_overrides = array( "test_form" => false );

    // Handle the upload using WordPress function
    // This moves the file to the uploads directory and performs security checks.
    $movefile = wp_handle_upload( $file, $upload_overrides );

    if ( $movefile && ! isset( $movefile["error"] ) ) {
        // File is uploaded successfully. Now create an attachment post for it.
        $wp_upload_dir = wp_upload_dir();
        $attachment = array(
            "guid"           => $wp_upload_dir["url"] . "/" . basename( $movefile["file"] ),
            "post_mime_type" => $movefile["type"],
            "post_title"     => preg_replace( "/\.[^.]+$/", "", basename( $movefile["file"] ) ),
            "post_content"   => "",
            "post_status"    => "inherit"
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $movefile["file"] );

        // Generate attachment metadata and update the attachment.
        require_once( ABSPATH . "wp-admin/includes/image.php" );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile["file"] );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        wp_send_json_success( array(
            "message"   => __( "File uploaded successfully!", "image-layout-to-gutenberg" ),
            "image_id"  => $attach_id,
            "image_url" => $movefile["url"], // URL of the uploaded file
            "file_info" => $movefile
        ) );
    } else {
        wp_send_json_error( $movefile["error"], 500 );
    }
}
