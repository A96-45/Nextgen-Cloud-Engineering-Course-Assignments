<?php
include __DIR__ . '/../config/database.php';

// Check if user is logged in
requireLogin();

// Get current user
$current_user = getCurrentUser();

// Fetch all students
$students = fetchAll("SELECT * FROM students ORDER BY created_at DESC");

// Handle delete student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_id'])) {
    $student_id = intval($_POST['delete_student_id']);
    $deleted = updateData("DELETE FROM students WHERE student_id = ?", 'i', [$student_id]);
    
    if ($deleted) {
        header("Location: dashboard.php?msg=deleted");
        exit();
    }
}

// Get success/info message
$message = '';
$message_type = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'deleted':
            $message = 'Student record deleted successfully';
            $message_type = 'success';
            break;
    }
}

$total_students = count($students);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Student Portal</title>
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
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
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
            font-size: 32px;
            margin-bottom: 8px;
            color: #1a202c;
        }

        .header-subtitle {
            color: #718096;
            font-size: 14px;
        }

        .btn-register-student {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-register-student:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
        }

        .stat-label {
            font-size: 14px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
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

        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 18px;
            color: #2d3748;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f7fafc;
            padding: 16px 24px;
            text-align: left;
            font-weight: 700;
            color: #4a5568;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        tr:hover {
            background: #f7fafc;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .student-name {
            font-weight: 600;
            color: #1a202c;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-primary {
            background: #bee3f8;
            color: #2c5282;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #bee3f8;
            color: #2c5282;
        }

        .btn-edit:hover {
            background: #90cdf4;
        }

        .btn-delete {
            background: #fed7d7;
            color: #c53030;
        }

        .btn-delete:hover {
            background: #fc8181;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .empty-state-desc {
            color: #718096;
            margin-bottom: 24px;
        }

        .empty-state .btn-register-student {
            margin-top: 16px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 32px;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 16px;
        }

        .modal-body {
            color: #4a5568;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .modal-footer button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
        }

        .btn-confirm-delete {
            background: #f56565;
            color: white;
        }

        .btn-confirm-delete:hover {
            background: #e53e3e;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 16px;
                flex-direction: column;
                gap: 12px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 12px 16px;
            }

            .action-buttons {
                flex-direction: column;
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
                    <div style="font-size: 12px; color: #718096;"><?php echo htmlspecialchars($current_user['email']); ?></div>
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
                <h1>Dashboard</h1>
                <p class="header-subtitle">Welcome back, <?php echo htmlspecialchars($current_user['username']); ?>! 👋</p>
            </div>
            <a href="student-form.php" class="btn-register-student">
                <span>➕</span>
                <span>Register Student</span>
            </a>
        </div>

        <!-- Success Message -->
        <?php if ($message && $message_type === 'success'): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?php echo $total_students; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Users</div>
                <div class="stat-value">1</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Last Updated</div>
                <div class="stat-value" style="font-size: 14px; color: #718096;">
                    <?php echo $total_students > 0 ? date('M d, Y', strtotime($students[0]['created_at'])) : 'N/A'; ?>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>Registered Students</h2>
                <span style="color: #718096; font-size: 14px;"><?php echo $total_students; ?> students</span>
            </div>

            <?php if ($total_students > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Gender</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><span class="badge badge-primary"><?php echo htmlspecialchars($student['student_number']); ?></span></td>
                                <td><span class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_of_study']); ?></td>
                                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small btn-edit" onclick="alert('Edit feature coming soon!')">Edit</button>
                                        <button class="btn-small btn-delete" onclick="openDeleteModal(<?php echo $student['student_id']; ?>, '<?php echo htmlspecialchars($student['first_name']); ?>')">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-title">No Students Registered Yet</div>
                    <div class="empty-state-desc">Start by registering your first student to see them appear here.</div>
                    <a href="student-form.php" class="btn-register-student">
                        <span>➕</span>
                        <span>Register First Student</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">Delete Student?</div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="studentNameInModal"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" id="deleteStudentId" name="delete_student_id">
                    <button type="submit" class="btn-confirm-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(studentId, studentName) {
            document.getElementById('deleteModal').classList.add('active');
            document.getElementById('deleteStudentId').value = studentId;
            document.getElementById('studentNameInModal').textContent = studentName;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Handle logout
        document.querySelector('button[name="logout"]').addEventListener('click', function(e) {
            fetch('logout.php', { method: 'POST' }).then(() => {
                window.location.href = 'login.php';
            });
        });
    </script>
</body>
</html>