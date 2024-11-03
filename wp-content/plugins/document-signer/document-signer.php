<?php
/*
Plugin Name: Document Signer
Description: Allow users to send and sign documents
Version: 1.0
Author: Your Name
*/

defined('ABSPATH') or die('Direct access not allowed');

class DocumentSigner {
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('init', array($this, 'register_post_type'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}document_signatures (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            document_id bigint(20) NOT NULL,
            user_email varchar(255) NOT NULL,
            signature_token varchar(255) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            signed_date datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function register_post_type() {
        register_post_type('document', array(
            'labels' => array(
                'name' => 'Documents',
                'singular_name' => 'Document'
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor'),
            'show_in_rest' => true
        ));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Document Signer',
            'Document Signer',
            'manage_options',
            'document-signer',
            array($this, 'admin_page'),
            'dashicons-media-document'
        );
    }

    public function admin_page() {
        include plugin_dir_path(__FILE__) . 'templates/admin-page.php';
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'document-signer',
            plugin_dir_url(__FILE__) . 'assets/css/style.css'
        );
        wp_enqueue_script(
            'document-signer',
            plugin_dir_url(__FILE__) . 'assets/js/script.js',
            array('jquery'),
            '1.0',
            true
        );
    }

    public function register_rest_routes() {
        register_rest_route('document-signer/v1', '/send-document', array(
            'methods' => 'POST',
            'callback' => array($this, 'send_document'),
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ));

        register_rest_route('document-signer/v1', '/sign-document', array(
            'methods' => 'POST',
            'callback' => array($this, 'sign_document'),
            'permission_callback' => '__return_true'
        ));
    }

    public function send_document($request) {
        $document_id = $request->get_param('document_id');
        $emails = $request->get_param('emails');
        
        foreach ($emails as $email) {
            $token = wp_generate_password(32, false);
            
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'document_signatures',
                array(
                    'document_id' => $document_id,
                    'user_email' => $email,
                    'signature_token' => $token,
                    'status' => 'pending'
                )
            );
            
            $sign_link = add_query_arg(array(
                'action' => 'sign',
                'token' => $token
            ), home_url());
            
            $this->send_signature_email($email, $sign_link);
        }
        
        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    private function send_signature_email($email, $sign_link) {
        $subject = 'Document Signature Request';
        $message = sprintf(
            'Please click the following link to sign the document: %s',
            $sign_link
        );
        
        wp_mail($email, $subject, $message);
    }

    public function sign_document($request) {
        $token = $request->get_param('token');
        $signature = $request->get_param('signature');
        
        global $wpdb;
        $signature_request = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}document_signatures WHERE signature_token = %s",
            $token
        ));
        
        if (!$signature_request) {
            return new WP_Error('invalid_token', 'Invalid signature token', array('status' => 400));
        }
        
        $wpdb->update(
            $wpdb->prefix . 'document_signatures',
            array(
                'status' => 'signed',
                'signed_date' => current_time('mysql')
            ),
            array('signature_token' => $token)
        );
        
        return new WP_REST_Response(array('status' => 'success'), 200);
    }
}

new DocumentSigner();