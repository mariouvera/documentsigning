<?php defined('ABSPATH') or die('Direct access not allowed'); ?>

<div class="wrap">
    <h1>Document Signer</h1>
    
    <div class="document-upload-section">
        <h2>Upload New Document</h2>
        <form id="document-upload-form">
            <input type="file" name="document" accept=".pdf,.doc,.docx" required>
            <button type="submit" class="button button-primary">Upload Document</button>
        </form>
    </div>

    <div class="document-list-section">
        <h2>Documents</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Recipients</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $documents = get_posts(array('post_type' => 'document'));
                foreach ($documents as $document) {
                    ?>
                    <tr>
                        <td><?php echo esc_html($document->post_title); ?></td>
                        <td class="recipients-cell" data-document-id="<?php echo esc_attr($document->ID); ?>">
                            <!-- Recipients will be loaded via JavaScript -->
                        </td>
                        <td class="status-cell">
                            <!-- Status will be loaded via JavaScript -->
                        </td>
                        <td>
                            <button class="button send-document" data-document-id="<?php echo esc_attr($document->ID); ?>">
                                Send for Signature
                            </button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Send Document Modal -->
    <div id="send-document-modal" class="modal">
        <div class="modal-content">
            <h3>Send Document for Signature</h3>
            <form id="send-document-form">
                <input type="hidden" name="document_id" id="modal-document-id">
                <div class="email-inputs">
                    <input type="email" name="emails[]" placeholder="Enter recipient email" required>
                    <input type="email" name="emails[]" placeholder="Enter recipient email" required>
                </div>
                <button type="submit" class="button button-primary">Send</button>
                <button type="button" class="button modal-close">Cancel</button>
            </form>
        </div>
    </div>
</div>