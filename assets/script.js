document.addEventListener('DOMContentLoaded', function () {
    // Validate email with SweetAlert2 and Bootstrap modal integration
    const validateEmail = () => {
        const emailInput = document.querySelector('input[name="email"]');
        const email = emailInput.value;
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailPattern.test(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.',
                footer: '<a href="#" id="helpLink">Need help with your email?</a>',
                confirmButtonText: 'OK'
            });

            // Use event delegation to ensure the listener is not added multiple times
            document.body.addEventListener('click', function helpLinkHandler(e) {
                if (e.target && e.target.id === 'helpLink') {
                    Swal.close(); // Close the SweetAlert2 alert

                    // Open the Bootstrap modal after SweetAlert2 is closed
                    const emailHelpModal = new bootstrap.Modal(document.getElementById('emailHelpModal'));
                    emailHelpModal.show();

                    // Remove this event listener after it runs to prevent duplication
                    document.body.removeEventListener('click', helpLinkHandler);
                }
            });

            return false; // Prevent form submission
        }
        return true; // Allow form submission
    };

    // Attach the email validation function to the form's submit event
    const registrationForm = document.querySelector('form[action="register.php"]');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function (e) {
            if (!validateEmail()) {
                e.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }
});