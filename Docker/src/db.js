const sqlite3 = require('sqlite3').verbose();
const path = require('path');

const dbPath = path.resolve(__dirname, '../attendance.db');
let db;

/**
 * Initializes the SQLite database.
 * Connects to attendance.db, enables foreign key support, and creates tables.
 * Returns a Promise that resolves when setup is complete.
 */
function initializeDatabase() {
  return new Promise((resolve, reject) => {
    db = new sqlite3.Database(dbPath, (err) => {
      if (err) {
        console.error('Error connecting to database:', err.message);
        return reject(err);
      }
      console.log('Connected to SQLite database at:', dbPath);

      // Enable foreign key constraints
      db.run('PRAGMA foreign_keys = ON;', (err) => {
        if (err) {
          console.error('Error enabling foreign keys:', err.message);
          return reject(err);
        }

        db.serialize(() => {
          // Create students table
          db.run(`
            CREATE TABLE IF NOT EXISTS students (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              name TEXT NOT NULL,
              email TEXT NOT NULL UNIQUE,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
          `, (err) => {
            if (err) {
              console.error('Error creating students table:', err.message);
              return reject(err);
            }
          });

          // Create attendance table
          db.run(`
            CREATE TABLE IF NOT EXISTS attendance (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              student_id INTEGER NOT NULL,
              date TEXT NOT NULL,
              status TEXT NOT NULL CHECK(status IN ('present', 'absent')),
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE,
              UNIQUE(student_id, date)
            )
          `, (err) => {
            if (err) {
              console.error('Error creating attendance table:', err.message);
              return reject(err);
            }
            console.log('Database tables verified/created successfully.');
            resolve();
          });
        });
      });
    });
  });
}

/**
 * Returns the active database connection.
 * Throws an error if the database has not been initialized.
 */
function getDatabase() {
  if (!db) {
    throw new Error('Database connection has not been initialized. Please call initializeDatabase first.');
  }
  return db;
}

module.exports = {
  initializeDatabase,
  getDatabase
};
