document.addEventListener('DOMContentLoaded', function() {
    // Admin Panel Functionality
    const sendDocumentButtons = document.querySelectorAll('.send-document');
    const modal = document.getElementById('send-document-modal');
    const modalClose = document.querySelector('.modal-close');
    const sendDocumentForm = document.getElementById('send-document-form');

    if (sendDocumentButtons) {
        sendDocumentButtons.forEach(button => {
            button.addEventListener('click', function() {
                const documentId = this.dataset.documentId;
                document.getElementById('modal-document-id').value = documentId;
                modal.style.display = 'block';
            });
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    if (sendDocumentForm) {
        sendDocumentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const documentId = formData.get('document_id');
            const emails = Array.from(formData.getAll('emails[]'));
            
            try {
                const response = await fetch('/wp-json/document-signer/v1/send-document', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': wpApiSettings.nonce
                    },
                    body: JSON.stringify({
                        document_id: documentId,
                        emails: emails
                    })
                });
                
                if (response.ok) {
                    modal.style.display = 'none';
                    alert('Document sent successfully!');
                } else {
                    throw new Error('Failed to send document');
                }
            } catch (error) {
                alert('Error sending document: ' + error.message);
            }
        });
    }

    // Signature Pad Functionality
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });

        document.getElementById('clear-signature').addEventListener('click', function() {
            signaturePad.clear();
        });

        document.getElementById('submit-signature').addEventListener('click', async function() {
            if (signaturePad.isEmpty()) {
                alert('Please provide a signature');
                return;
            }

            const signatureData = signaturePad.toDataURL();
            const token = new URLSearchParams(window.location.search).get('token');

            try {
                const response = await fetch('/wp-json/document-signer/v1/sign-document', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token: token,
                        signature: signatureData
                    })
                });

                if (response.ok) {
                    alert('Document signed successfully!');
                    window.location.href = '/my-documents/';
                } else {
                    throw new Error('Failed to sign document');
                }
            } catch (error) {
                alert('Error signing document: ' + error.message);
            }
        });
    }
});