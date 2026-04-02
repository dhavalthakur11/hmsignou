// ── Password visibility toggle 
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    input.type = (input.type === 'password') ? 'text' : 'password';
}

// ── Password strength meter (register form)
const pwInput = document.getElementById('password');
const pwBar   = document.getElementById('pwBar');
const pwHint  = document.getElementById('pwHint');

if (pwInput && document.getElementById('registerForm')) {
    pwInput.addEventListener('input', () => {
        const val   = pwInput.value;
        const score = getPasswordScore(val);
        const pct   = (score / 4) * 100;

        pwBar.style.width = pct + '%';
        pwBar.className   = 'pw-bar ' + ['', 'weak', 'fair', 'good', 'strong'][score];

        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        pwHint.textContent = val.length ? labels[score] : '';
    });
}

function getPasswordScore(pw) {
    let score = 0;
    if (pw.length >= 8)               score++;
    if (/[A-Z]/.test(pw))             score++;
    if (/[0-9]/.test(pw))             score++;
    if (/[^A-Za-z0-9]/.test(pw))      score++;
    return score;
}

// ── Login form validation 
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        const email = loginForm.querySelector('#email').value.trim();
        const pw    = loginForm.querySelector('#password').value;
        let   valid = true;

        clearErrors();

        if (!isValidEmail(email)) {
            showError('email', 'Enter a valid email address.');
            valid = false;
        }
        if (pw.length < 1) {
            showError('password', 'Password is required.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
}

// ── Register form validation 
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
        clearErrors();
        let valid = true;

        const name  = registerForm.querySelector('#name').value.trim();
        const email = registerForm.querySelector('#email').value.trim();
        const pw    = registerForm.querySelector('#password').value;

        if (name.length < 2) {
            showError('name', 'Name must be at least 2 characters.');
            valid = false;
        }
        if (!isValidEmail(email)) {
            showError('email', 'Enter a valid email address.');
            valid = false;
        }
        if (pw.length < 8) {
            showError('password', 'Password must be at least 8 characters.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
}

// ── Helpers
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('input-error');
    const hint = document.createElement('span');
    hint.className   = 'field-error';
    hint.textContent = message;
    field.parentNode.appendChild(hint);
}

function clearErrors() {
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    document.querySelectorAll('.field-error').forEach(el => el.remove());
}