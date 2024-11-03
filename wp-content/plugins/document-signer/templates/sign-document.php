<?php defined('ABSPATH') or die('Direct access not allowed');

$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
global $wpdb;

$signature_request = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}document_signatures WHERE signature_token = %s",
    $token
));

if (!$signature_request || $signature_request->status === 'signed') {
    wp_die('Invalid or expired signature request');
}

$document = get_post($signature_request->document_id);
?>

<div class="document-signing-page">
    <h1>Sign Document: <?php echo esc_html($document->post_title); ?></h1>
    
    <?php if (!is_user_logged_in()): ?>
        <div class="registration-form">
            <h2>Quick Registration</h2>
            <form id="quick-register-form">
                <input type="email" name="email" value="<?php echo esc_attr($signature_request->user_email); ?>" readonly>
                <input type="text" name="name" placeholder="Your Full Name" required>
                <input type="password" name="password" placeholder="Choose a Password" required>
                <button type="submit">Register & Continue</button>
            </form>
        </div>
    <?php else: ?>
        <div class="document-content">
            <?php echo apply_filters('the_content', $document->post_content); ?>
        </div>
        
        <div class="signature-section">
            <h3>Sign Document</h3>
            <canvas id="signature-pad"></canvas>
            <div class="signature-controls">
                <button id="clear-signature">Clear</button>
                <button id="submit-signature" class="button-primary">Sign Document</button>
            </div>
        </div>
    <?php endif; ?>
</div>