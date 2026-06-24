<?php
include __DIR__ . '/../config/database.php';

// Check if user is logged in
requireLogin();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_of_study = intval($_POST['year_of_study'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    
    // Validation
    if (empty($student_number) || empty($first_name) || empty($last_name) || empty($email) || empty($course) || empty($year_of_study) || empty($gender)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($year_of_study < 1 || $year_of_study > 6) {
        $error = 'Year of study must be between 1 and 6';
    } else {
        // Check if student number already exists
        $check_number = fetchOne("SELECT student_id FROM students WHERE student_number = ?", 's', [$student_number]);
        if ($check_number) {
            $error = 'Student number already exists';
        } else {
            // Check if email already exists
            $check_email = fetchOne("SELECT student_id FROM students WHERE email = ?", 's', [$email]);
            if ($check_email) {
                $error = 'Email already registered';
            } else {
                // Insert student
                $student_id = insertData(
                    "INSERT INTO students (student_number, first_name, last_name, email, phone, course, year_of_study, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    'ssssssss',
                    [$student_number, $first_name, $last_name, $email, $phone, $course, strval($year_of_study), $gender]
                );
                
                if ($student_id) {
                    $success = 'Student registered successfully! Redirecting...';
                    header("refresh:2;url=dashboard.php");
                } else {
                    $error = 'An error occurred. Please try again.';
                }
            }
        }
    }
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student | Student Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f7fafc;
            color: #2d3748;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
            text-decoration: none;
        }

        .navbar-brand-icon {
            font-size: 28px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .user-name {
            font-weight: 600;
            color: #2d3748;
        }

        .btn-logout {
            padding: 8px 16px;
            background: #f56565;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #e53e3e;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title h1 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #1a202c;
        }

        .header-subtitle {
            color: #718096;
            font-size: 14px;
        }

        .btn-back {
            padding: 10px 20px;
            background: #e2e8f0;
            color: #2d3748;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: #cbd5e0;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 40px;
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

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background-color: #f7fafc;
        }

        input::placeholder {
            color: #a0aec0;
        }

        .required-indicator {
            color: #f56565;
            margin-left: 4px;
        }

        .radio-group {
            display: flex;
            gap: 24px;
            margin-top: 8px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="radio"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .radio-option label {
            margin-bottom: 0;
            cursor: pointer;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
            color: #4a5568;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            margin-top: 32px;
            justify-content: flex-start;
        }

        .btn-submit {
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-cancel {
            padding: 14px 32px;
            background: #e2e8f0;
            color: #2d3748;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
        }

        .form-info {
            background: #edf2f7;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border-left: 4px solid #667eea;
        }

        .form-info-title {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .form-info-text {
            font-size: 14px;
            color: #4a5568;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 12px;
            }

            .container {
                padding: 20px 16px;
            }

            .form-container {
                padding: 24px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
            }

            .radio-group {
                flex-wrap: wrap;
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
    <!-- Navbar -->
    <div class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <span class="navbar-brand-icon">📚</span>
            <span>Student Portal</span>
        </a>
        <div class="navbar-right">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($current_user['username']); ?></div>
                </div>
            </div>
            <form method="POST" style="display: inline;">
                <button type="submit" name="logout" class="btn-logout">Logout</button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <div class="header-title">
                <h1>Register Student</h1>
                <p class="header-subtitle">Add a new student to the system</p>
            </div>
            <a href="dashboard.php" class="btn-back">
                <span>←</span>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <!-- Error/Success Messages -->
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

            <!-- Info Box -->
            <div class="form-info">
                <div class="form-info-title">📝 Before You Start</div>
                <div class="form-info-text">
                    Fill in all the required fields marked with <span class="required-indicator">*</span> below. All information is mandatory for student registration.
                </div>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="student-form.php" id="studentForm">
                <!-- Row 1: Student Number & Course -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="student_number">Student Number <span class="required-indicator">*</span></label>
                        <input 
                            type="text" 
                            id="student_number" 
                            name="student_number" 
                            placeholder="e.g., STU2024001"
                            required
                            value="<?php echo htmlspecialchars($_POST['student_number'] ?? ''); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="course">Course <span class="required-indicator">*</span></label>
                        <input 
                            type="text" 
                            id="course" 
                            name="course" 
                            placeholder="e.g., Computer Science"
                            required
                            value="<?php echo htmlspecialchars($_POST['course'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <!-- Row 2: First Name & Last Name -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required-indicator">*</span></label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            placeholder="John"
                            required
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required-indicator">*</span></label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            placeholder="Doe"
                            required
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <!-- Row 3: Email & Phone -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address <span class="required-indicator">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="john.doe@example.com"
                            required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            placeholder="+254712345678"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <!-- Row 4: Year of Study -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="year_of_study">Year of Study <span class="required-indicator">*</span></label>
                        <input 
                            type="number" 
                            id="year_of_study" 
                            name="year_of_study" 
                            min="1" 
                            max="6"
                            required
                            value="<?php echo htmlspecialchars($_POST['year_of_study'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <!-- Gender Selection -->
                <div class="form-group">
                    <label>Gender <span class="required-indicator">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input 
                                type="radio" 
                                id="gender_male" 
                                name="gender" 
                                value="Male"
                                required
                                <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'checked' : ''; ?>
                            >
                            <label for="gender_male">Male</label>
                        </div>
                        <div class="radio-option">
                            <input 
                                type="radio" 
                                id="gender_female" 
                                name="gender" 
                                value="Female"
                                required
                                <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'checked' : ''; ?>
                            >
                            <label for="gender_female">Female</label>
                        </div>
                        <div class="radio-option">
                            <input 
                                type="radio" 
                                id="gender_other" 
                                name="gender" 
                                value="Other"
                                required
                                <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'checked' : ''; ?>
                            >
                            <label for="gender_other">Other</label>
                        </div>
                        <div class="radio-option">
                            <input 
                                type="radio" 
                                id="gender_prefer_not" 
                                name="gender" 
                                value="Prefer not to say"
                                required
                                <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Prefer not to say') ? 'checked' : ''; ?>
                            >
                            <label for="gender_prefer_not">Prefer not to say</label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Register Student</button>
                    <a href="dashboard.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Handle logout
        const logoutBtn = document.querySelector('button[name="logout"]');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fetch('logout.php', { method: 'POST' }).then(() => {
                    window.location.href = 'login.php';
                });
            });
        }

        // Form validation
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const yearOfStudy = parseInt(document.getElementById('year_of_study').value);
            
            if (isNaN(yearOfStudy) || yearOfStudy < 1 || yearOfStudy > 6) {
                e.preventDefault();
                alert('Year of study must be between 1 and 6');
                return false;
            }
        });
    </script>
</body>
</html>