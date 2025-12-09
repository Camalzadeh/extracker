document.addEventListener('DOMContentLoaded', () => {
    // DOM Elementlərini seçirik
    const showLoginButton = document.getElementById('show-login');
    const showRegisterButton = document.getElementById('show-register');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginMessageDiv = document.getElementById('login-message');
    const registerMessageDiv = document.getElementById('register-message');

    // Funksiya: Formlar arasında keçid etmək
    const setActiveForm = (activeForm, inactiveForm, activeButton, inactiveButton) => {
        inactiveForm.classList.remove('active');
        activeForm.classList.add('active');
        inactiveButton.classList.remove('active');
        activeButton.classList.add('active');
    };

    // Keçid düymələrinin hadisə dinləyiciləri
    showLoginButton.addEventListener('click', () => {
        setActiveForm(loginForm, registerForm, showLoginButton, showRegisterButton);
    });

    showRegisterButton.addEventListener('click', () => {
        setActiveForm(registerForm, loginForm, showRegisterButton, showLoginButton);
    });

    // -----------------------------------------------------
    // ƏSAS FUNKSİYA: URL-dən Gələn Mesajları İdarə Etmək
    // -----------------------------------------------------

    const handleUrlMessages = () => {
        const params = new URLSearchParams(window.location.search);
        const error = params.get('error');
        const success = params.get('success');

        if (!error && !success) return;

        const displayMessage = (type, code) => {
            let message = '';
            let targetDiv = null;
            let formToActivate = loginForm; // Varsayılan olaraq Login formunu göstər

            // Səhv və ya uğur kodlarına uyğun mesajları təyin etmək
            switch (code) {
                // LOGIN Səhvləri
                case 'invalid_credentials':
                    message = 'Invalid username or password. Please try again.';
                    targetDiv = loginMessageDiv;
                    break;
                case 'login_empty':
                    message = 'Username and Password fields cannot be empty.';
                    targetDiv = loginMessageDiv;
                    break;

                // REGISTER Səhvləri
                case 'register_empty':
                    message = 'All fields are required for registration.';
                    targetDiv = registerMessageDiv;
                    formToActivate = registerForm;
                    break;
                case 'password_mismatch':
                    message = 'Passwords do not match.';
                    targetDiv = registerMessageDiv;
                    formToActivate = registerForm;
                    break;
                case 'user_exists':
                    message = 'This username or email is already registered.';
                    targetDiv = registerMessageDiv;
                    formToActivate = registerForm;
                    break;

                // ÜMUMİ Səhvlər / UĞUR
                case 'server_error':
                    message = 'A server error occurred. Please try again later.';
                    targetDiv = loginMessageDiv;
                    break;
                case 'registered': // Uğurlu qeydiyyat
                    message = 'Registration successful! You can now log in.';
                    targetDiv = loginMessageDiv;
                    break;
                default:
                    message = 'An unknown issue occurred.';
                    targetDiv = loginMessageDiv;
            }

            if (targetDiv && message) {
                targetDiv.textContent = message;
                targetDiv.style.display = 'block';
                targetDiv.className = type === 'error' ? 'error-message' : 'success-message';

                // Mesajı göstərmək üçün uyğun forma keçid edirik
                if (formToActivate === registerForm) {
                    setActiveForm(registerForm, loginForm, showRegisterButton, showLoginButton);
                } else {
                    setActiveForm(loginForm, registerForm, showLoginButton, showRegisterButton);
                }
            }
        };

        if (error) {
            displayMessage('error', error);
        } else if (success) {
            displayMessage('success', success);
        }

        // URL-dən sorğu parametrlərini təmizləyirik
        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    };

    // Səhifə yüklənən kimi funksiyanı çağırırıq
    handleUrlMessages();
});