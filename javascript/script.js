// Function to navigate between pages
function navigateTo(page) {
    window.location.href = page;
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

document.addEventListener('DOMContentLoaded', function() {
    // If register form exists, wire up submit to save profile
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
            // ensure form starts blank on initial load / refresh
            try { registerForm.reset(); } catch (err) {}

            // if page is shown from BFCache (back/forward), also reset
            window.addEventListener('pageshow', function (evt) {
                try {
                    if (evt.persisted) registerForm.reset();
                } catch (err) {}
            });

            registerForm.addEventListener('submit', function(e) {
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
            // Allow form to submit normally to store.php. Clear local cached profile
            // and reset the form so fields are blank on next visit/refresh.
            try { localStorage.removeItem('studentProfile'); } catch (err) {}
            setTimeout(function() { try { registerForm.reset(); } catch (err) {} }, 60);
        });

        // clear error while user types into password fields
        const pwdEl = document.getElementById('password');
        const confirmEl = document.getElementById('confirm_password');
        const errorEl = document.getElementById('passwordError');
        if (pwdEl) pwdEl.addEventListener('input', function() { if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; } pwdEl.classList.remove('input-error'); });
        if (confirmEl) confirmEl.addEventListener('input', function() { if (errorEl) { errorEl.style.display = 'none'; errorEl.textContent = ''; } confirmEl.classList.remove('input-error'); });
    }

    // If profile page exists, try fetching server session profile first, then fall back to localStorage
    const profileCard = document.getElementById('profileCard');
    if (profileCard) {
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
            profileCard.innerHTML = html;
        }

        // Try server-side profile (requires PHP session)
        fetch('../Backend/get_profile.php', { credentials: 'same-origin' })
            .then(resp => {
                if (!resp.ok) throw new Error('no-server-profile');
                return resp.json();
            })
            .then(data => {
                if (data && data.user) {
                    renderProfile(data.user);
                    // cache to localStorage for offline/view-only pages
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
                // fallback to localStorage if server data not available
                const stored = getStoredProfile();
                if (!stored) {
                    profileCard.innerHTML = '<p class="muted">No profile data found. Please <a href="register.html">register</a>.</p>';
                } else {
                    renderProfile(stored);
                }
            })
            .finally(() => {
                // wire logout button
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
});