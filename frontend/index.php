<?php
include __DIR__ . '/../config/database.php';

// Check if user is logged in
$is_logged_in = isLoggedIn();
$current_user = null;
if ($is_logged_in) {
    $current_user = getCurrentUser();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Manage Your Academic Journey</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            color: #2d3748;
            line-height: 1.6;
        }

        /* Navbar */
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

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .navbar-links {
            display: flex;
            gap: 24px;
            list-style: none;
        }

        .navbar-links a {
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .navbar-links a:hover {
            color: #667eea;
        }

        .navbar-actions {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .btn-nav {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-nav-login {
            background: #e2e8f0;
            color: #2d3748;
        }

        .btn-nav-login:hover {
            background: #cbd5e0;
        }

        .btn-nav-signup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-nav-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.6);
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

        .user-menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .user-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: #718096;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 32px;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-icon {
            font-size: 64px;
            margin-bottom: 24px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 32px;
            opacity: 0.95;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 14px 32px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            padding: 14px 32px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Features Section */
        .features {
            padding: 80px 32px;
            background: #f7fafc;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 48px;
        }

        .section-title h2 {
            font-size: 36px;
            margin-bottom: 16px;
            color: #1a202c;
        }

        .section-title p {
            font-size: 16px;
            color: #718096;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: white;
            padding: 32px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            animation: slideUp 0.6s ease-out;
            animation-fill-mode: both;
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

        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: #1a202c;
        }

        .feature-card p {
            font-size: 14px;
            color: #718096;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 80px 32px;
            background: white;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .stat-item p {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 32px;
            text-align: center;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta h2 {
            font-size: 36px;
            margin-bottom: 16px;
        }

        .cta p {
            font-size: 18px;
            margin-bottom: 32px;
            opacity: 0.95;
        }

        /* Footer */
        .footer {
            background: #1a202c;
            color: #a0aec0;
            padding: 40px 32px;
            text-align: center;
        }

        .footer p {
            font-size: 14px;
            margin-bottom: 12px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #667eea;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 12px 16px;
                flex-direction: column;
                gap: 12px;
            }

            .navbar-menu {
                flex-direction: column;
                gap: 16px;
                width: 100%;
            }

            .navbar-links {
                flex-direction: column;
                gap: 12px;
            }

            .navbar-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn-nav {
                width: 100%;
                justify-content: center;
            }

            .hero {
                padding: 40px 16px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .features {
                padding: 40px 16px;
            }

            .section-title h2 {
                font-size: 24px;
            }

            .stats {
                padding: 40px 16px;
            }

            .cta {
                padding: 40px 16px;
            }

            .cta h2 {
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
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <span class="navbar-brand-icon">📚</span>
            <span>Student Portal</span>
        </a>
        <div class="navbar-menu">
            <ul class="navbar-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#stats">Stats</a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <div class="navbar-actions">
                <?php if ($is_logged_in && $current_user): ?>
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></div>
                        <div class="user-menu">
                            <div class="user-name"><?php echo htmlspecialchars($current_user['username']); ?></div>
                            <div class="user-role">Logged In</div>
                        </div>
                    </div>
                    <a href="dashboard.php" class="btn-nav btn-nav-signup">Go to Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav btn-nav-login">Login</a>
                    <a href="registration.php" class="btn-nav btn-nav-signup">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-icon">📚</div>
            <h1>Welcome to Student Portal</h1>
            <p>Manage your academic journey with a modern, intuitive student management system</p>
            <div class="hero-buttons">
                <?php if ($is_logged_in && $current_user): ?>
                    <a href="dashboard.php" class="btn-primary">View Dashboard</a>
                    <a href="student-form.php" class="btn-secondary">Register Student</a>
                <?php else: ?>
                    <a href="registration.php" class="btn-primary">Get Started</a>
                    <a href="login.php" class="btn-secondary">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Everything you need to manage students effectively</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Secure Authentication</h3>
                    <p>Enterprise-grade security with bcrypt password hashing and session management to protect user data.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Student Management</h3>
                    <p>Effortlessly register, view, and manage student records with a clean, intuitive interface.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Real-time Updates</h3>
                    <p>Instant database synchronization ensures your data is always up-to-date across all platforms.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Responsive Design</h3>
                    <p>Seamlessly works on desktop, tablet, and mobile devices for access anytime, anywhere.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats" id="stats">
        <div class="stats-container">
            <div class="section-title">
                <h2>By The Numbers</h2>
                <p>Trusted by institutions worldwide</p>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>10K+</h3>
                    <p>Students Managed</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Institutions</p>
                </div>
                <div class="stat-item">
                    <h3>99.9%</h3>
                    <p>Uptime SLA</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="about">
        <div class="cta-content">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of institutions using Student Portal to streamline their student management processes.</p>
            <?php if ($is_logged_in && $current_user): ?>
                <a href="dashboard.php" class="btn-primary">Go to Dashboard</a>
            <?php else: ?>
                <a href="registration.php" class="btn-primary">Create Account Now</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-links">
            <a href="#features">Features</a>
            <a href="#stats">Stats</a>
            <a href="#about">About</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </div>
        <p>&copy; 2026 Student Portal. All rights reserved. Built with ❤️ for education.</p>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>