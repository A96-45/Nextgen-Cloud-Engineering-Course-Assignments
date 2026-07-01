const express = require('express');
const cors = require('cors');
const path = require('path');
const db = require('./db');

const app = express();
const PORT = process.env.PORT || 5000;

// 1. CORS Middleware (allow all origins)
app.use(cors());

// 2. JSON Body Parser Middleware
app.use(express.json());

// 3. Root endpoint: GET / (API version)
app.get('/', (req, res) => {
  res.json({ message: "Student Attendance API", version: "1.0.0" });
});

// 4. Serve static frontend assets from src/public
app.use(express.static(path.join(__dirname, 'public')));

// 5. Health Check endpoint: GET /api/health
app.get('/api/health', (req, res) => {
  res.json({ status: "ok", message: "Server is running" });
});

// 6. Import and Mount routes (passing the app instance)
const studentRoutes = require('./routes/students');
const attendanceRoutes = require('./routes/attendance');
studentRoutes(app);
attendanceRoutes(app);

// 7. Handle 404 errors (route not found)
app.use((req, res) => {
  res.status(404).json({ success: false, message: "Route not found" });
});

// 8. Error handling middleware (catch-all, logged, and return clean error response)
app.use((err, req, res, next) => {
  console.error('Unhandled Application Error:', err);
  res.status(500).json({
    success: false,
    message: 'Internal server error occurred'
  });
});

// Initialize database, then start listening
db.initializeDatabase()
  .then(() => {
    app.listen(PORT, () => {
      console.log(`Server running on http://localhost:${PORT}`);
    });
  })
  .catch((err) => {
    console.error('Failed to initialize database. Server cannot start.', err.message);
    process.exit(1);
  });

module.exports = app; // Exported for testing if needed
