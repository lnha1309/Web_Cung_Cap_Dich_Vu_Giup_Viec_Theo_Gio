<script>
    document.addEventListener('DOMContentLoaded', () => {
        // FAQ toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function () {
                const faqItem = this.parentElement;
                const isActive = faqItem.classList.contains('active');

                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });

                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });

        // Account dropdown toggle
        const accountToggle = document.getElementById('accountMenuToggle');
        const accountDropdown = document.getElementById('accountMenuDropdown');

        if (accountToggle && accountDropdown) {
            accountToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                accountDropdown.classList.toggle('open');
                
                // Close notification dropdown if open
                const notificationDropdown = document.getElementById('notificationDropdown');
                if (notificationDropdown) {
                    notificationDropdown.classList.remove('show');
                }
            });

            document.addEventListener('click', () => {
                accountDropdown.classList.remove('open');
            });

            accountDropdown.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        }
    });
</script>

<script src="{{ asset('js/notifications.js') }}"></script>
