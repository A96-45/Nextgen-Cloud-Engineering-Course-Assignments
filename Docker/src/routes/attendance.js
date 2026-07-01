const { getDatabase } = require('../db');

/**
 * Registers attendance management routes on the Express app.
 * @param {Express.Application} app - The Express application instance.
 */
module.exports = function(app) {

  // ENDPOINT 1: POST /api/attendance (Mark attendance for a student on a date)
  app.post('/api/attendance', (req, res) => {
    const { student_id, date, status } = req.body;

    // Validation
    if (!student_id) {
      return res.status(400).json({
        success: false,
        message: 'student_id is required'
      });
    }

    // Date format validation (YYYY-MM-DD)
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!date || !dateRegex.test(date)) {
      return res.status(400).json({
        success: false,
        message: 'Date is required and must be in YYYY-MM-DD format'
      });
    }

    // Date validity validation
    const parsedDate = new Date(date);
    if (isNaN(parsedDate.getTime())) {
      return res.status(400).json({
        success: false,
        message: 'Invalid date value'
      });
    }

    // Status validation
    if (!status || (status !== 'present' && status !== 'absent')) {
      return res.status(400).json({
        success: false,
        message: 'Status must be "present" or "absent"'
      });
    }

    const db = getDatabase();

    // Verify student exists before marking attendance
    db.get('SELECT id FROM students WHERE id = ?', [student_id], (err, student) => {
      if (err) {
        console.error('Database error in POST /api/attendance check:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!student) {
        return res.status(400).json({
          success: false,
          message: 'Student does not exist'
        });
      }

      // Use INSERT OR REPLACE for duplicate date handling (as defined by UNIQUE(student_id, date))
      db.run(
        'INSERT OR REPLACE INTO attendance (student_id, date, status) VALUES (?, ?, ?)',
        [student_id, date, status],
        function(err) {
          if (err) {
            console.error('Database error in POST /api/attendance insert:', err.message);
            return res.status(500).json({
              success: false,
              message: 'Database error occurred'
            });
          }

          res.status(200).json({
            success: true,
            message: 'Attendance marked',
            attendanceId: this.lastID
          });
        }
      );
    });
  });

  // ENDPOINT 2: GET /api/attendance (Get all attendance records, with optional filters)
  app.get('/api/attendance', (req, res) => {
    const { student_id, date, status } = req.query;
    const db = getDatabase();

    let query = `
      SELECT a.id, a.student_id, s.name AS student_name, a.date, a.status 
      FROM attendance a
      JOIN students s ON a.student_id = s.id
    `;
    const conditions = [];
    const params = [];

    if (student_id) {
      conditions.push('a.student_id = ?');
      params.push(student_id);
    }
    if (date) {
      conditions.push('a.date = ?');
      params.push(date);
    }
    if (status) {
      conditions.push('a.status = ?');
      params.push(status);
    }

    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }
    query += ' ORDER BY a.date DESC, student_name ASC';

    db.all(query, params, (err, rows) => {
      if (err) {
        console.error('Database error in GET /api/attendance:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      res.status(200).json({
        success: true,
        attendance: rows || []
      });
    });
  });

  // ENDPOINT 3: GET /api/attendance/:id (Get single attendance record)
  app.get('/api/attendance/:id', (req, res) => {
    const { id } = req.params;
    const db = getDatabase();

    const query = `
      SELECT a.id, a.student_id, s.name AS student_name, a.date, a.status
      FROM attendance a
      JOIN students s ON a.student_id = s.id
      WHERE a.id = ?
    `;

    db.get(query, [id], (err, row) => {
      if (err) {
        console.error('Database error in GET /api/attendance/:id:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!row) {
        return res.status(404).json({
          success: false,
          message: 'Attendance record not found'
        });
      }

      res.status(200).json({
        success: true,
        attendance: row
      });
    });
  });

  // ENDPOINT 4: PUT /api/attendance/:id (Update attendance status)
  app.put('/api/attendance/:id', (req, res) => {
    const { id } = req.params;
    const { status } = req.body;

    if (!status || (status !== 'present' && status !== 'absent')) {
      return res.status(400).json({
        success: false,
        message: 'Status must be "present" or "absent"'
      });
    }

    const db = getDatabase();

    // Check if record exists
    db.get('SELECT id FROM attendance WHERE id = ?', [id], (err, row) => {
      if (err) {
        console.error('Database error in PUT /api/attendance check:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!row) {
        return res.status(404).json({
          success: false,
          message: 'Attendance record not found'
        });
      }

      db.run('UPDATE attendance SET status = ? WHERE id = ?', [status, id], function(err) {
        if (err) {
          console.error('Database error in PUT /api/attendance update:', err.message);
          return res.status(500).json({
            success: false,
            message: 'Database error occurred'
          });
        }

        res.status(200).json({
          success: true,
          message: 'Attendance updated'
        });
      });
    });
  });

  // ENDPOINT 5: GET /api/attendance/student/:student_id (Get all attendance records for a student)
  app.get('/api/attendance/student/:student_id', (req, res) => {
    const { student_id } = req.params;
    const db = getDatabase();

    // Verify student exists
    db.get('SELECT id, name FROM students WHERE id = ?', [student_id], (err, student) => {
      if (err) {
        console.error('Database error in GET /api/attendance/student/:student_id check:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!student) {
        return res.status(404).json({
          success: false,
          message: 'Student not found'
        });
      }

      db.all(
        'SELECT id, date, status FROM attendance WHERE student_id = ? ORDER BY date DESC',
        [student_id],
        (err, rows) => {
          if (err) {
            console.error('Database error in GET /api/attendance/student/:student_id query:', err.message);
            return res.status(500).json({
              success: false,
              message: 'Database error occurred'
            });
          }

          res.status(200).json({
            success: true,
            student_id: student.id,
            student_name: student.name,
            records: rows || []
          });
        }
      );
    });
  });

  // EXTRA ENDPOINT: DELETE /api/attendance/:id (Delete attendance record)
  app.delete('/api/attendance/:id', (req, res) => {
    const { id } = req.params;
    const db = getDatabase();

    db.get('SELECT id FROM attendance WHERE id = ?', [id], (err, row) => {
      if (err) {
        console.error('Database error in DELETE /api/attendance check:', err.message);
        return res.status(500).json({
          success: false,
          message: 'Database error occurred'
        });
      }

      if (!row) {
        return res.status(404).json({
          success: false,
          message: 'Attendance record not found'
        });
      }

      db.run('DELETE FROM attendance WHERE id = ?', [id], function(err) {
        if (err) {
          console.error('Database error in DELETE /api/attendance delete:', err.message);
          return res.status(500).json({
            success: false,
            message: 'Database error occurred'
          });
        }

        res.status(200).json({
          success: true,
          message: 'Attendance record deleted'
        });
      });
    });
  });
};
