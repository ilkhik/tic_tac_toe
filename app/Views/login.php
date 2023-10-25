<!DOCTYPE html>
<html>
    <head>
        <title>Вход</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="/js/login.js"></script>
    </head>
    <body>
        <h3 id="action-header">Войдите</h3>
        <form id="form" action="javascript:submitLogin();">
            <input id="login" placeholder="Имя пользователя" required autofocus>
            <input id="password" placeholder="Пароль" type="password" required>
            <span style="color: red" id="error-text"></span>
            <button id="submit-button">Войти</button>
        </form>
        <a id="sign-up-link" href="" onclick="switchToSignUp();return false;">Регистрация</a>
        <a id="login-link" hidden href="" onclick="switchToLogin();return false;">Вход</a>
    </body>
</html>
