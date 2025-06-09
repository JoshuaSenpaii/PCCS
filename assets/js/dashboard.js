document.addEventListener("DOMContentLoaded", function() {

    document.querySelectorAll(".delete-user-btn").forEach(function(button) {
        button.addEventListener("click", function(e) {
            if (!confirm("Are you sure you want to delete this user?")) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll(".delete-product-btn").forEach(function(button) {
        button.addEventListener("click", function(e) {
            if (!confirm("Are you sure you want to delete this product?")) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll(".editable-field").forEach(function(input) {
        input.addEventListener("focus", function() {
            this.dataset.originalValue = this.value;
        });

        input.addEventListener("blur", function() {
            if (this.value !== this.dataset.originalValue) {
                this.closest("form").submit();
            }
        });
    });

});
