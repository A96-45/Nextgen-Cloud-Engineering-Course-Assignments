const { getDatabase } = require('../db');

/**
 * Registers student management routes on the Express app.
 * @param {Express.Application} app - The Express application instance.
 */
module.exports = function(app) {
  
  // ENDPOINT 1: POST /api/students (Add a new student)
  app.post('/api/students', (req, res) => {
    const { name, email } = req.body;

    // Validation
    if (!name || typeof name !== 'string' || name.trim() === '' || !email || typeof email !== 'string' || email.trim() === '') {
      return res.status(400).json({
        success: false,
        message: 'Name and email are required'
      });
    }

    // Check email format (contains @ and a domain)
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid email format'
      });
    }

    const db = getDatabase();
    const cleanName = name.trim();
    const cleanEmail = email.trim();

    db.run(
      'INSERT INTO students (name, email) VALUES (?, ?)',
      [cleanName, cleanEmail],
      function(err) {
        if (err) {
          if (err.message.includes('UNIQUE constraint failed')) {
            return res.status(400).json({
              success: false,
              message: 'Email already exists'
            });
          }
          console.error('Database error in POST /api/students:', err.message);
          return res.status(500).json({
            success: false,
            message: 'Database error occurred'
          });
        }

        res.status(200).json({
          success: true,
          message: 'Student added successfully',
          studentId: this.lastID
        });
      }
    );
  });

  // ENDPOINT 2: GET /api/students (Get all students)
  app.get('/api/students', (req, res) => {
    const db = getDatabase();

    db.all('SELECT id, name, email FROM students ORDER BY name ASC', [], (err, rows) => {
      if (err) {
        console.error('Database error in GET /api/students:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      res.status(200).json({
        success: true,
        students: rows || []
      });
    });
  });

  // ENDPOINT 3: GET /api/students/:id (Get single student details)
  app.get('/api/students/:id', (req, res) => {
    const { id } = req.params;
    const db = getDatabase();

    db.get('SELECT id, name, email FROM students WHERE id = ?', [id], (err, row) => {
      if (err) {
        console.error('Database error in GET /api/students/:id:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!row) {
        return res.status(404).json({
          success: false,
          message: 'Student not found'
        });
      }

      res.status(200).json({
        success: true,
        student: row
      });
    });
  });

  // ENDPOINT 4: DELETE /api/students/:id (Delete a student)
  app.delete('/api/students/:id', (req, res) => {
    const { id } = req.params;
    const db = getDatabase();

    // Check if student exists first to return 404 if appropriate
    db.get('SELECT id FROM students WHERE id = ?', [id], (err, row) => {
      if (err) {
        console.error('Database error in checking student before DELETE:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!row) {
        return res.status(404).json({
          success: false,
          message: 'Student not found'
        });
      }

      // Perform deletion
      db.run('DELETE FROM students WHERE id = ?', [id], function(err) {
        if (err) {
          console.error('Database error in DELETE /api/students/:id:', err.message);
          return res.status(500).json({
            success: false,
            message: 'Database error occurred'
          });
        }

        res.status(200).json({
          success: true,
          message: 'Student deleted'
        });
      });
    });
  });
};
