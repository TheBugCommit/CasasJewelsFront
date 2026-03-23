document.addEventListener('DOMContentLoaded', function() {
    const limitSelect = document.querySelector('.catalog-limit-select');
    if (limitSelect) {
        limitSelect.addEventListener('change', function() {
            window.location.href = this.value;
        });
    }
});
