<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Variable to track if we're redirecting to success/failed naturally
        let isRedirecting = false;
        
        // Listen for successful polling redirects to avoid releasing lock right before
        // the server processes the success.
        const originalHref = window.location.href;
        const observer = new MutationObserver(function() {
            if (window.location.href !== originalHref) {
                isRedirecting = true;
            }
        });
        
        // When the user navigates away or closes the tab, send a beacon to unlock
        function releaseLock() {
            if (isRedirecting) return;
            
            const unlockUrl = "{{ route('payment.unlock', $booking->id) }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // navigator.sendBeacon is perfect for sending data right as the page is unloading
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                navigator.sendBeacon(unlockUrl, formData);
            } else {
                // Fallback for older browsers
                fetch(unlockUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken }),
                    keepalive: true // Ensures request completes even if page unloads
                });
            }
        }

        window.addEventListener('beforeunload', releaseLock);
        window.addEventListener('pagehide', releaseLock);
        
        // Add a global function to let the polling script tell us it's a natural redirect
        window.setRedirecting = function() {
            isRedirecting = true;
        };
    });
</script>
