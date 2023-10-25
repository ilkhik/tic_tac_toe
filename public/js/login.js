async function submitLogin() {
    const login = document.getElementById('login').value;
    const password = document.getElementById('password').value;
    const errorTextElement = document.getElementById('error-text');
    const submitButton = document.getElementById('submit-button');
    errorTextElement.innerHTML = '';
    submitButton.hidden = true;
    
    const response = await fetch('/api/login', {
        method: 'POST',
        body: JSON.stringify({
            login,
            password
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    });
    const result = await response.json();
    if (response.ok) {
        sessionStorage.setItem('token', result.token);
        sessionStorage.setItem('refreshToken', result.refresh);
        location.pathname = '/';
    } else {
        errorTextElement.innerHTML = result.message;
        submitButton.hidden = false;
    }
}

async function submitSignUp() {
    const login = document.getElementById('login').value;
    const password = document.getElementById('password').value;
    const errorTextElement = document.getElementById('error-text');
    const submitButton = document.getElementById('submit-button');
    errorTextElement.innerHTML = '';
    submitButton.hidden = true;
    
    const response = await fetch('/api/sign_up', {
        method: 'POST',
        body: JSON.stringify({
            login,
            password
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    });
    const result = await response.json();
    if (response.ok) {
        sessionStorage.setItem('token', result.token);
        sessionStorage.setItem('refreshToken', result.refresh);
        location.pathname = '/';
    } else {
        errorTextElement.innerHTML = result.message;
        submitButton.hidden = false;
    }
}

function switchToSignUp() {
    document.getElementById('action-header').innerHTML = 'Зарегистрируйтесь';
    document.getElementById('form').action = "javascript:submitSignUp();";
    document.getElementById('sign-up-link').hidden = true;
    document.getElementById('login-link').hidden = false;
    document.getElementById('login').focus();
}

function switchToLogin() {
    document.getElementById('action-header').innerHTML = 'Войдите';
    document.getElementById('form').action = "javascript:submitLogin();";
    document.getElementById('sign-up-link').hidden = false;
    document.getElementById('login-link').hidden = true;
    document.getElementById('login').focus();
}