const API = '/api';

// ── DOM references ──────────────────────────────────────────────────────────
const $ = id => document.getElementById(id);

// Forms
const addStudentForm        = $('add-student-form');
const studentNameInput      = $('student-name');
const studentEmailInput     = $('student-email');
const studentMsg            = $('student-form-message');

const markAttendanceForm    = $('mark-attendance-form');
const attendanceSelect      = $('attendance-student-select');
const attendanceDateInput   = $('attendance-date');
const attendanceMsg         = $('attendance-form-message');

// Tabs
const tabBtns               = document.querySelectorAll('.tab-btn');
const tabPanes              = document.querySelectorAll('.tab-pane');

// Students tab
const studentsTbody         = $('students-table-body');
const studentsEmpty         = $('students-empty');
const studentsTable         = $('students-table');
const refreshStudentsBtn    = $('refresh-students-btn');

// Attendance tab
const attendanceTbody       = $('attendance-table-body');
const attendanceEmpty       = $('attendance-empty');
const attendanceTable       = $('attendance-table');
const refreshAttendanceBtn  = $('refresh-attendance-btn');
const filterStudent         = $('filter-student');
const filterDate            = $('filter-date');
const filterStatus          = $('filter-status');
const clearFiltersBtn       = $('clear-filters-btn');

// Summary tab
const summaryStudentSelect  = $('summary-student-select');
const summaryStats          = $('summary-stats');
const summaryDefault        = $('summary-default');
const summaryTbody          = $('summary-table-body');
const summaryTable          = $('summary-table');
const summaryEmpty          = $('summary-empty');

// Modal
const editModal             = $('edit-modal');
const modalInfo             = $('modal-info');
const modalRecordId         = $('modal-record-id');
const modalStatusSelect     = $('modal-status-select');
const saveEditBtn           = $('save-edit-btn');
const cancelEditBtn         = $('cancel-edit-btn');

// ── Init ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  setDefaultDate();
  fetchStudents();
  fetchAttendance();
  bindEvents();
});

function setDefaultDate() {
  const d = new Date();
  attendanceDateInput.value = d.toISOString().slice(0, 10);
}

// ── Helpers ─────────────────────────────────────────────────────────────────
function showMsg(el, text, isError) {
  el.textContent = text;
  el.className = 'msg ' + (isError ? 'error' : 'success');
  setTimeout(() => { el.textContent = ''; el.className = 'msg'; }, 4000);
}

function fmtDate(str) {
  if (!str) return '';
  const d = new Date(str + 'T00:00:00');
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function esc(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ── Students ─────────────────────────────────────────────────────────────────
async function fetchStudents() {
  try {
    const res  = await fetch(`${API}/students`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    renderStudents(data.students);
    populateStudentDropdowns(data.students);
  } catch (e) {
    console.error(e);
    studentsTbody.innerHTML = `<tr><td colspan="4" style="color:#cf222e;padding:12px">Error loading students.</td></tr>`;
  }
}

function renderStudents(list) {
  if (list.length === 0) {
    studentsTable.style.display = 'none';
    studentsEmpty.style.display = 'block';
    return;
  }
  studentsTable.style.display = '';
  studentsEmpty.style.display = 'none';
  studentsTbody.innerHTML = list.map(s => `
    <tr>
      <td>${s.id}</td>
      <td>${esc(s.name)}</td>
      <td>${esc(s.email)}</td>
      <td>
        <button class="btn-delete" data-id="${s.id}" data-name="${esc(s.name)}">Delete</button>
      </td>
    </tr>
  `).join('');
}

function populateStudentDropdowns(list) {
  const opts = list.map(s => `<option value="${s.id}">${esc(s.name)}</option>`).join('');

  // Mark attendance select
  const prevA = attendanceSelect.value;
  attendanceSelect.innerHTML = '<option value="" disabled selected>Select student</option>' + opts;
  if (list.some(s => s.id == prevA)) attendanceSelect.value = prevA;

  // Filter select
  const prevF = filterStudent.value;
  filterStudent.innerHTML = '<option value="">All students</option>' + opts;
  if (list.some(s => s.id == prevF)) filterStudent.value = prevF;

  // Summary select
  const prevS = summaryStudentSelect.value;
  summaryStudentSelect.innerHTML = '<option value="" disabled selected>Select a student</option>' + opts;
  if (list.some(s => s.id == prevS)) {
    summaryStudentSelect.value = prevS;
    fetchStudentSummary(prevS);
  }
}

async function addStudent(name, email) {
  try {
    const res  = await fetch(`${API}/students`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email })
    });
    const data = await res.json();
    if (data.success) {
      showMsg(studentMsg, 'Student added.', false);
      addStudentForm.reset();
      fetchStudents();
    } else {
      showMsg(studentMsg, data.message || 'Error', true);
    }
  } catch (e) {
    showMsg(studentMsg, 'Network error.', true);
  }
}

async function deleteStudent(id, name) {
  if (!confirm(`Delete "${name}" and all their attendance records?`)) return;
  try {
    const res  = await fetch(`${API}/students/${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      fetchStudents();
      fetchAttendance();
    } else {
      alert(data.message || 'Error deleting student.');
    }
  } catch (e) {
    alert('Network error.');
  }
}

// ── Attendance ───────────────────────────────────────────────────────────────
async function fetchAttendance() {
  try {
    const params = new URLSearchParams();
    if (filterStudent.value) params.set('student_id', filterStudent.value);
    if (filterDate.value)    params.set('date',       filterDate.value);
    if (filterStatus.value)  params.set('status',     filterStatus.value);

    const res  = await fetch(`${API}/attendance?${params}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    renderAttendance(data.attendance);
  } catch (e) {
    console.error(e);
    attendanceTbody.innerHTML = `<tr><td colspan="5" style="color:#cf222e;padding:12px">Error loading records.</td></tr>`;
  }
}

function renderAttendance(list) {
  if (list.length === 0) {
    attendanceTable.style.display = 'none';
    attendanceEmpty.style.display = 'block';
    return;
  }
  attendanceTable.style.display = '';
  attendanceEmpty.style.display = 'none';
  attendanceTbody.innerHTML = list.map(r => `
    <tr>
      <td>${r.id}</td>
      <td>${esc(r.student_name)}</td>
      <td>${fmtDate(r.date)}</td>
      <td><span class="badge ${r.status}">${r.status}</span></td>
      <td>
        <button class="btn-edit" data-id="${r.id}" data-name="${esc(r.student_name)}" data-date="${r.date}" data-status="${r.status}">Edit</button>
        <button class="btn-delete" data-id="${r.id}">Delete</button>
      </td>
    </tr>
  `).join('');
}

async function markAttendance(student_id, date, status) {
  try {
    const res  = await fetch(`${API}/attendance`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ student_id, date, status })
    });
    const data = await res.json();
    if (data.success) {
      showMsg(attendanceMsg, 'Attendance marked.', false);
      attendanceSelect.value = '';
      fetchAttendance();
    } else {
      showMsg(attendanceMsg, data.message || 'Error', true);
    }
  } catch (e) {
    showMsg(attendanceMsg, 'Network error.', true);
  }
}

async function updateAttendance(id, status) {
  try {
    const res  = await fetch(`${API}/attendance/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status })
    });
    const data = await res.json();
    if (data.success) {
      closeModal();
      fetchAttendance();
      if (summaryStudentSelect.value) fetchStudentSummary(summaryStudentSelect.value);
    } else {
      alert(data.message || 'Error updating record.');
    }
  } catch (e) {
    alert('Network error.');
  }
}

async function deleteAttendance(id) {
  if (!confirm('Delete this attendance record?')) return;
  try {
    const res  = await fetch(`${API}/attendance/${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      fetchAttendance();
      if (summaryStudentSelect.value) fetchStudentSummary(summaryStudentSelect.value);
    } else {
      alert(data.message || 'Error deleting record.');
    }
  } catch (e) {
    alert('Network error.');
  }
}

// ── Summary ──────────────────────────────────────────────────────────────────
async function fetchStudentSummary(id) {
  if (!id) return;
  try {
    const res  = await fetch(`${API}/attendance/student/${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    renderSummary(data);
  } catch (e) {
    console.error(e);
  }
}

function renderSummary(data) {
  summaryDefault.style.display = 'none';
  summaryStats.style.display   = 'block';

  const records = data.records || [];
  const total   = records.length;
  const present = records.filter(r => r.status === 'present').length;
  const absent  = total - present;
  const pct     = total ? Math.round((present / total) * 100) : 0;

  $('stats-total').textContent   = total;
  $('stats-present').textContent = present;
  $('stats-absent').textContent  = absent;
  $('stats-pct').textContent     = pct + '%';

  if (records.length === 0) {
    summaryTable.style.display = 'none';
    summaryEmpty.style.display = 'block';
    return;
  }
  summaryTable.style.display = '';
  summaryEmpty.style.display = 'none';

  summaryTbody.innerHTML = records.map(r => `
    <tr>
      <td>${r.id}</td>
      <td>${fmtDate(r.date)}</td>
      <td><span class="badge ${r.status}">${r.status}</span></td>
    </tr>
  `).join('');
}

// ── Modal ────────────────────────────────────────────────────────────────────
function openModal(id, name, date, status) {
  modalRecordId.value      = id;
  modalInfo.textContent    = `${name} — ${fmtDate(date)}`;
  modalStatusSelect.value  = status;
  editModal.style.display  = 'flex';
}

function closeModal() {
  editModal.style.display = 'none';
}

// ── Events ───────────────────────────────────────────────────────────────────
function bindEvents() {
  // Tab switching
  tabBtns.forEach(btn => btn.addEventListener('click', () => {
    tabBtns.forEach(b => b.classList.remove('active'));
    tabPanes.forEach(p => { p.style.display = 'none'; p.classList.remove('active'); });
    btn.classList.add('active');
    const pane = $(btn.dataset.tab);
    pane.style.display = 'block';
    pane.classList.add('active');
  }));

  // Add student
  addStudentForm.addEventListener('submit', e => {
    e.preventDefault();
    const name  = studentNameInput.value.trim();
    const email = studentEmailInput.value.trim();
    if (!name || !email) return showMsg(studentMsg, 'All fields are required.', true);
    addStudent(name, email);
  });

  // Mark attendance
  markAttendanceForm.addEventListener('submit', e => {
    e.preventDefault();
    const sid    = attendanceSelect.value;
    const date   = attendanceDateInput.value;
    const status = markAttendanceForm.querySelector('input[name="status"]:checked').value;
    if (!sid)  return showMsg(attendanceMsg, 'Please select a student.', true);
    if (!date) return showMsg(attendanceMsg, 'Please enter a date.', true);
    markAttendance(sid, date, status);
  });

  // Refresh buttons
  refreshStudentsBtn.addEventListener('click', fetchStudents);
  refreshAttendanceBtn.addEventListener('click', fetchAttendance);

  // Filters
  filterStudent.addEventListener('change', fetchAttendance);
  filterDate.addEventListener('change', fetchAttendance);
  filterStatus.addEventListener('change', fetchAttendance);
  clearFiltersBtn.addEventListener('click', () => {
    filterStudent.value = '';
    filterDate.value    = '';
    filterStatus.value  = '';
    fetchAttendance();
  });

  // Summary dropdown
  summaryStudentSelect.addEventListener('change', e => fetchStudentSummary(e.target.value));

  // Event delegation: students table
  studentsTbody.addEventListener('click', e => {
    if (e.target.classList.contains('btn-delete')) {
      deleteStudent(e.target.dataset.id, e.target.dataset.name);
    }
  });

  // Event delegation: attendance table
  attendanceTbody.addEventListener('click', e => {
    const t = e.target;
    if (t.classList.contains('btn-delete')) {
      deleteAttendance(t.dataset.id);
    } else if (t.classList.contains('btn-edit')) {
      openModal(t.dataset.id, t.dataset.name, t.dataset.date, t.dataset.status);
    }
  });

  // Modal
  saveEditBtn.addEventListener('click', () => {
    updateAttendance(modalRecordId.value, modalStatusSelect.value);
  });
  cancelEditBtn.addEventListener('click', closeModal);
  editModal.addEventListener('click', e => {
    if (e.target === editModal) closeModal();
  });
}
