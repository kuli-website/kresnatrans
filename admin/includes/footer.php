                </div>
            </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Suppress console warnings for tracking prevention (non-critical)
        if (typeof console !== 'undefined') {
            const originalWarn = console.warn;
            console.warn = function(...args) {
                // Filter out tracking prevention warnings
                const message = args.join(' ');
                if (message.includes('Tracking Prevention') || message.includes('tracking prevention')) {
                    return; // Suppress these warnings
                }
                originalWarn.apply(console, args);
            };
        }
        
        // Auto-dismiss alerts
        try {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        } catch (e) {
            // Silently handle errors if Bootstrap is not loaded
            console.error('Error initializing alerts:', e);
        }
    </script>
</body>
</html>

