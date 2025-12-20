document.addEventListener("DOMContentLoaded", () => {

    // --- ELEMENTS ---
    const loginFormWrapper = document.getElementById("login-form");
    const registerFormWrapper = document.getElementById("register-form");

    const loginTab = document.getElementById("show-login");
    const registerTab = document.getElementById("show-register");

    // --- FORM SWITCH ---
    const setActiveForm = (activeFormWrapper, inactiveFormWrapper, activeTab, inactiveTab) => {
        activeFormWrapper.classList.add("active");
        inactiveFormWrapper.classList.remove("active");

        activeTab.classList.add("active");
        inactiveTab.classList.remove("active");
    };

    loginTab.addEventListener("click", () => {
        setActiveForm(loginFormWrapper, registerFormWrapper, loginTab, registerTab);
    });

    registerTab.addEventListener("click", () => {
        setActiveForm(registerFormWrapper, loginFormWrapper, registerTab, loginTab);
    });

});
