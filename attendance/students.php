<?php
// pages/students.php

$filterClass = $_GET['class'] ?? '';
$classes     = getClasses($db);
$allStudents = getStudents($db, $filterClass);
?>

<div class="section-header">
    <select onchange="window.location='index.php?action=students&class='+this.value" style="width:auto">
        <option value="">All Classes</option>
        <?php foreach ($classes as $c): ?>
        <option value="<?= esc($c) ?>" <?= $filterClass === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" onclick="document.getElementById('add-modal').classList.add('open')">
        + Add Student
    </button>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Student ID</th>
                <th>Class</th>
                <th>Attendance Rate</th>
                <th>Breakdown</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($allStudents)): ?>
            <tr>
                <td colspan="6" class="empty">
                    <div class="empty-icon">👥</div>
                    <p>No students yet. Add one to get started.</p>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($allStudents as $s):
                $stats = getStudentStats($db, $s['id']);
                $total = array_sum($stats);
                $pct   = $total > 0 ? round($stats['present'] / $total * 100) : 0;
                $barColor = $pct >= 80 ? 'var(--green)' : ($pct >= 60 ? 'var(--yellow)' : 'var(--red)');
            ?>
            <tr>
                <td>
                    <div style="font-weight:500"><?= esc($s['name']) ?></div>
                </td>
                <td style="font-family:monospace;font-size:.85rem;color:var(--muted)"><?= esc($s['student_id']) ?></td>
                <td><span class="badge badge-none"><?= esc($s['class']) ?></span></td>
                <td style="min-width:140px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div class="progress" style="flex:1">
                            <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>"></div>
                        </div>
                        <span style="font-size:.8rem;color:var(--muted)"><?= $pct ?>%</span>
                    </div>
                </td>
                <td style="font-size:.75rem;color:var(--muted)">
                    <span style="color:var(--green)"><?= $stats['present'] ?> P</span> ·
                    <span style="color:var(--red)"><?= $stats['absent'] ?> A</span> ·
                    <span style="color:var(--yellow)"><?= $stats['late'] ?> L</span> ·
                    <span style="color:var(--blue)"><?= $stats['excused'] ?> E</span>
                </td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="index.php?action=reports&student=<?= $s['id'] ?>" class="btn btn-ghost btn-sm">View</a>
                        <a href="index.php?action=delete_student&id=<?= $s['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete <?= esc(addslashes($s['name'])) ?>? This will also remove all their attendance records.')">
                            Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Student Modal -->
<div class="modal-overlay" id="add-modal">
    <div class="modal">
        <div class="modal-title">Add New Student</div>
        <form method="post" action="index.php?action=add_student">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Jane Smith" required autofocus>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="e.g. S007" required>
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <input type="text" name="class" placeholder="e.g. Grade 10-A" required list="class-list">
                    <datalist id="class-list">
                        <?php foreach ($classes as $c): ?>
                        <option value="<?= esc($c) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px">
                <button type="submit" class="btn btn-primary">Add Student</button>
                <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('add-modal').classList.remove('open')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('add-modal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
