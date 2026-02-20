// Function to navigate between pages
function navigateTo(page) {
    if (!page) return;
    // respect absolute URLs
    if (/^https?:\/\//.test(page) || page.startsWith('/')) {
        window.location.href = page;
    } else {
        // combine with base href if present so relative paths work consistently
        const base = document.querySelector('base')?.getAttribute('href') || '';
        window.location.href = base + page;
    }
}

// Helper to get stored profile from localStorage
function getStoredProfile() {
    try {
        const raw = localStorage.getItem('studentProfile');
        return raw ? JSON.parse(raw) : null;
    } catch (err) {
        return null;
    }
}

// Error Display Function
function showErrorMessage(title, message, backLink = 'Templates/register.html') {
    const container = document.createElement('div');
    container.className = 'message-container';
    
    const messageBox = document.createElement('div');
    messageBox.className = 'message-box error-message';
    
    messageBox.innerHTML = `
        <h2>❌ ${title}</h2>
        <p><strong>Error Details:</strong></p>
        <div class="error-details">${message}</div>
        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            Please check your information and try again.
        </p>
        <a href="${backLink}">Go Back to Registration</a>
    `;
    
    document.body.innerHTML = '';
    document.body.appendChild(container);
    container.appendChild(messageBox);
}

// Success Display Function
function showSuccessMessage(title, message, redirectLink = null, redirectTime = 3000) {
    const container = document.createElement('div');
    container.className = 'message-container';
    
    const messageBox = document.createElement('div');
    messageBox.className = 'message-box success-message';
    
    messageBox.innerHTML = `
        <h2>✅ ${title}</h2>
        <p>${message}</p>
        <p style="font-size: 14px; color: #666;">
            ${redirectLink ? `Redirecting in ${redirectTime / 1000} seconds...` : ''}
        </p>
        ${!redirectLink ? '<a href="backend/login-page.php">Proceed to Login</a>' : ''}
    `;
    
    document.body.innerHTML = '';
    document.body.appendChild(container);
    container.appendChild(messageBox);
    
    if (redirectLink) {
        setTimeout(() => {
            window.location.href = redirectLink;
        }, redirectTime);
    }
}

// Validate Email Format
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validate Form Fields
function validateFormFields(formData) {
    const { rollno, fullname, email, password, confirmPassword, contactno, stream } = formData;
    
    if (!rollno || !fullname || !email || !password || !confirmPassword || !contactno || !stream) {
        return {
            valid: false,
            error: "All fields are required. Please fill in all the fields."
        };
    }
    
    if (!validateEmail(email)) {
        return {
            valid: false,
            error: "Invalid email format. Please enter a valid email address."
        };
    }
    
    if (password !== confirmPassword) {
        return {
            valid: false,
            error: "Passwords do not match. Please ensure both passwords are identical."
        };
    }
    
    if (password.length < 6) {
        return {
            valid: false,
            error: "Password must be at least 6 characters long."
        };
    }
    
    return { valid: true };
}

// Global error handler
window.addEventListener('error', function(event) {
    console.error('Error caught:', event.error);
    showErrorMessage(
        'An Error Occurred',
        event.error.message || 'An unexpected error occurred. Please try again.',
        'Templates/register.html'
    );
});

// helper functions that don't depend on DOM can go here
function isValidContact(val) {
    return /^[7-9][0-9]{9}$/.test(val);
}

function validateRegistrationFields(e) {
    const emailInput = document.getElementById('email');
    const contactInput = document.getElementById('contact');
    const contactError = document.getElementById('contactError');
    let emailError;
    if (emailInput) {
        emailError = emailInput.nextElementSibling;
        if (!emailError || !emailError.classList.contains('form-error')) {
            emailError = document.createElement('div');
            emailError.className = 'form-error';
            emailError.setAttribute('role','alert');
            emailError.setAttribute('aria-live','polite');
            emailError.style.display = 'none';
            emailInput.parentNode.insertBefore(emailError, emailInput.nextSibling);
        }
    }

    // email restriction
    if (emailInput) {
        const val = emailInput.value.trim();
        if (!val.endsWith('@gmail.com')) {
            emailError.textContent = 'Email must be a gmail address.';
            emailError.style.display = 'block';
            emailInput.focus();
            e.preventDefault();
            return false;
        } else {
            emailError.style.display = 'none';
        }
    }

    // contact restrictions
    if (contactInput) {
        let val = contactInput.value.replace(/[^0-9]/g, '');
        contactInput.value = val; // sanitize
        if (!isValidContact(val)) {
            if (contactError) contactError.style.display = 'block';
            contactInput.focus();
            e.preventDefault();
            return false;
        } else if (contactError) {
            contactError.style.display = 'none';
        }
        // prefix international code when sending
        contactInput.value = '+91' + val;
    }

    return true;
}

// registration-specific handlers
function handleRegistrationSubmit(e) {
    const pwdEl = document.getElementById('password');
    const confirmEl = document.getElementById('confirm_password');
    const errorEl = document.getElementById('passwordError');

    const pwd = pwdEl ? pwdEl.value : '';
    const confirm = confirmEl ? confirmEl.value : '';

    // clear previous error
    function clearPasswordError() {
        if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; }
        if (pwdEl) pwdEl.classList.remove('input-error');
        if (confirmEl) confirmEl.classList.remove('input-error');
    }

    function showPasswordError(msg) {
        if (errorEl) { errorEl.textContent = msg; errorEl.style.display = 'block'; }
        if (pwdEl) pwdEl.classList.add('input-error');
        if (confirmEl) confirmEl.classList.add('input-error');
    }

    if (pwd !== confirm) {
        showPasswordError('Passwords do not match.');
        if (confirmEl) confirmEl.focus();
        e.preventDefault();
        return;
    }

    clearPasswordError();
    // perform extra registration field validation
    validateRegistrationFields(e);

    // Allow form to submit normally to store.php. Clear local cached profile
    // and reset the form so fields are blank on next visit/refresh.
    try { localStorage.removeItem('studentProfile'); } catch (err) {}
    setTimeout(function() { try { document.getElementById('registerForm').reset(); } catch (err) {} }, 60);
}

// Profile rendering helpers
function renderProfile(obj) {
    // Support both server keys and localStorage keys
    const roll = obj.rollno || obj.roll || '—';
    const fullname = obj.fullname || obj.name || '—';
    const email = obj.email || '—';
    const contact = obj.contactno || obj.contact || '—';
    const stream = obj.stream || '—';

    const rows = [
        ['Roll No.', roll],
        ['Full Name', fullname],
        ['Email', email],
        ['Contact', contact],
        ['Stream', stream]
    ];
    let html = '<div class="profile-grid">';
    rows.forEach(r => {
        html += `<div class="profile-row"><div class="label">${r[0]}</div><div class="value">${r[1]}</div></div>`;
    });
    html += '</div>';
    const profileCard = document.getElementById('profileCard');
    if (profileCard) profileCard.innerHTML = html;
}

function loadProfile() {
    const profileCard = document.getElementById('profileCard');
    if (!profileCard) return;

    fetch('backend/get_profile.php', { credentials: 'same-origin' })
        .then(resp => {
            if (!resp.ok) throw new Error('no-server-profile');
            return resp.json();
        })
        .then(data => {
            if (data && data.user) {
                renderProfile(data.user);
                try {
                    localStorage.setItem('studentProfile', JSON.stringify({
                        roll: data.user.rollno || data.user.roll,
                        fullname: data.user.fullname,
                        email: data.user.email,
                        contact: data.user.contactno || data.user.contact,
                        stream: data.user.stream
                    }));
                } catch (err) {}
            } else {
                throw new Error('no-user');
            }
        })
        .catch(() => {
            const stored = getStoredProfile();
            if (!stored) {
                profileCard.innerHTML = '<p class="muted">No profile data found. Please <a href="register.html">register</a>.</p>';
            } else {
                renderProfile(stored);
            }
        })
        .finally(() => {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    try { localStorage.removeItem('studentProfile'); } catch (err) {}
                    alert('Logged out.');
                    navigateTo('Index.html');
                });
            }
        });
}

// common initialization executed once DOM is ready
function initializePage() {
    // registration form handling
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        try { registerForm.reset(); } catch (err) {}
        window.addEventListener('pageshow', function (evt) {
            try { if (evt.persisted) registerForm.reset(); } catch (err) {}
        });
        registerForm.addEventListener('submit', handleRegistrationSubmit);

        const contactInput = document.getElementById('contact');
        const contactError = document.getElementById('contactError');
        if (contactInput) {
            contactInput.addEventListener('input', () => {
                let val = contactInput.value.replace(/[^0-9]/g, '');
                contactInput.value = val;
                if (!isValidContact(val)) {
                    if (contactError) contactError.style.display = 'block';
                } else {
                    if (contactError) contactError.style.display = 'none';
                }
            });
        }

        const pwdEl = document.getElementById('password');
        const confirmEl = document.getElementById('confirm_password');
        const errorEl = document.getElementById('passwordError');
        if (pwdEl) pwdEl.addEventListener('input', function() { if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; } pwdEl.classList.remove('input-error'); });
        if (confirmEl) confirmEl.addEventListener('input', function() { if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; } confirmEl.classList.remove('input-error'); });
    }

    // load profile info if page has profileCard
    loadProfile();
}

document.addEventListener('DOMContentLoaded', initializePage);