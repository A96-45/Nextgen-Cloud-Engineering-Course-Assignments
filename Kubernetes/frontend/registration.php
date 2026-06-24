<?php
include __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'All fields are required';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } elseif (strlen($username) > 50) {
        $error = 'Username must not exceed 50 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } else {
        $check_username = fetchOne("SELECT user_id FROM users WHERE username = ?", 's', [$username]);
        if ($check_username) {
            $error = 'Username already taken';
        } else {
            $check_email = fetchOne("SELECT user_id FROM users WHERE email = ?", 's', [$email]);
            if ($check_email) {
                $error = 'Email already registered';
            } else {
                $password_hash = hashPassword($password);
                $user_id = insertData(
                    "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)",
                    'sss',
                    [$username, $email, $password_hash]
                );
                
                if ($user_id) {
                    $success = 'Account created successfully! Redirecting to login...';
                    header("refresh:2;url=login.php");
                } else {
                    $error = 'An error occurred. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Student Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 480px;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 48px 40px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-circle {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: white;
        }

        h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background-color: #f7fafc;
        }

        input::placeholder {
            color: #a0aec0;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background-color: #fed7d7;
            color: #c53030;
            border-left: 4px solid #c53030;
        }

        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }

        .btn-register {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-register:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.4);
        }

        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #4a5568;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .password-strength {
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: none;
            align-items: center;
            gap: 6px;
        }

        .password-strength.active {
            display: flex;
        }

        .strength-bar {
            width: 60px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
        }

        .strength-weak .strength-fill {
            width: 33%;
            background: #fc8181;
        }

        .strength-fair .strength-fill {
            width: 66%;
            background: #fbd38d;
        }

        .strength-strong .strength-fill {
            width: 100%;
            background: #9ae6b4;
        }

        .requirements {
            font-size: 12px;
            color: #718096;
            margin-top: 12px;
            line-height: 1.6;
        }

        .requirements ul {
            margin-left: 16px;
            margin-top: 4px;
        }

        .requirements li {
            margin-bottom: 4px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 24px;
            }

            h1 {
                font-size: 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="logo-circle">📚</div>
                <h1>Create Account</h1>
                <p class="subtitle">Join the Student Portal</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="registration.php" id="registrationForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required minlength="3" maxlength="50" autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="your.email@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required minlength="8">
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <span id="strengthText">Weak</span>
                    </div>
                    <div class="requirements">
                        <strong>Password must have:</strong>
                        <ul>
                            <li>At least 8 characters</li>
                            <li>Mix of uppercase & lowercase</li>
                            <li>At least one number</li>
                            <li>At least one special character (!@#$%)</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Re-enter your password" required minlength="8">
                </div>

                <button type="submit" class="btn-register">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[!@#$%^&*]/)) strength++;
            return strength;
        }

        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            if (this.value.length === 0) {
                passwordStrength.classList.remove('active');
            } else {
                passwordStrength.classList.add('active');
                if (strength <= 1) {
                    passwordStrength.className = 'password-strength active strength-weak';
                    strengthText.textContent = 'Weak';
                } else if (strength <= 2) {
                    passwordStrength.className = 'password-strength active strength-fair';
                    strengthText.textContent = 'Fair';
                } else {
                    passwordStrength.className = 'password-strength active strength-strong';
                    strengthText.textContent = 'Strong';
                }
            }
        });

        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            const strength = calculatePasswordStrength(password);
            if (strength < 3) {
                e.preventDefault();
                alert('Password is not strong enough!');
                return false;
            }
        });
    </script>
</body>
</html>