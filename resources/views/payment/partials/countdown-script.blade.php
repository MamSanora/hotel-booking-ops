<script>
(function () {
    let secs = 60;
    const countEl = document.getElementById('countdown');
    
    // Determine where to redirect when expired
    // If it's a stay extension, they are checked-in already, so redirect to their booking.
    // Otherwise, redirect to the general dashboard.
    const expiredRedirectUrl = "{{ $booking->booking_status === 'checked-in' ? route('guest.booking.show', $booking->id) : route('guest.dashboard') }}";

    const timer = setInterval(() => {
        secs--;
        const m = String(Math.floor(secs / 60)).padStart(2, '0');
        const s = String(secs % 60).padStart(2, '0');
        
        if (countEl) {
            countEl.textContent = `${m}:${s}`;
        }
        
        if (secs <= 0) {
            clearInterval(timer);
            if (countEl) {
                countEl.textContent = 'Expired';
                countEl.style.color = '#ef4444';
            }
            alert('Your payment session has expired to free up the room for other guests.');
            
            // Setting this flag prevents the unlock-script from firing an unnecessary beacon
            // since we're naturally redirecting due to timeout (and the lock will expire on backend anyway)
            // Wait, actually, we DO want the beacon to fire to unlock it immediately! 
            // So we DON'T call window.setRedirecting().
            
            window.location.href = expiredRedirectUrl;
        }
    }, 1000);

    // Make timer accessible globally so polling can clear it on success
    window.paymentCountdownTimer = timer;
})();
</script>
