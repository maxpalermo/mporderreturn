<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded');

        const anchor = document.querySelector('a.btn[href*="pdf-order-return"]');
        console.log("BTN ANCHOR:", anchor, anchor.href);

        if (anchor) {
            anchor.href = '{$moduleFrontController}';
            anchor.addEventListener('click', async (ev) => {
                ev.preventDefault();
                const url = anchor.href;

                console.log("URL:", url);

                const formData = new FormData();
                formData.append('id_order_return', '{$id_order_return}');
                formData.append('action', 'printPdf');
                formData.append('ajax', '1');
                formData.append('id_employee', '{$id_employee}');

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const json = await response.json();
                    console.log("Response received:", json);

                    if (!json.success) {
                        alert(json.error || 'An error occurred while generating the PDF');
                        return false;
                    }

                    // Convert base64 to blob
                    const byteCharacters = atob(json.pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], { type: 'application/pdf' });

                    // Create download link
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = json.filename || 'modulo_reso.pdf';

                    // Opzione 1: Download diretto
                    //a.click();

                    // Opzione 2: Apri in una nuova finestra
                    window.open(blobUrl, '_blank');

                    // Pulisci l'URL dopo il download
                    setTimeout(() => {
                        URL.revokeObjectURL(blobUrl);
                    }, 100);

                    console.log("PDF generato con successo");
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while generating the PDF');
                }

                return false;
            });
        }
    });
</script>