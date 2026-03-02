<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $basePath ?? ''; ?>/assets/css/login.css">
    <link rel="icon" type="image/png" href="https://img.freepik.com/free-icon/black-male-user-symbol_318-60703.jpg">
</head>
<body>
    <div class="wrapper">
        <div class="logo">
            <img src="https://img.freepik.com/free-icon/black-male-user-symbol_318-60703.jpg" alt="User">
        </div>
        <div class="text-center mt-4 name">Login</div>
        <form class="p-3 mt-3" id="form-login">
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="text" name="userName" id="user" placeholder="Username">
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" id="password" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Login</button>
        </form>
    </div>
    <script>
        const LOGIN_PROCESS_URL = '<?php echo ($basePath ?? '') . '/login/process'; ?>';
        const REDIRECT_URL = '<?php echo ($basePath ?? '') . '/'; ?>';
    </script>
    <script src="<?php echo $basePath ?? ''; ?>/assets/js/login.js"></script>
</body>
</html>
