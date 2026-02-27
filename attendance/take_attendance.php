<?php
// pages/take_attendance.php

$today         = date('Y-m-d');
$selectedDate  = $_GET['date']  ?? $today;
$filterClass   = $_GET['class'] ?? '';
$classes       = getClasses($db);
$allStudents   = getStudents($db, $filterClass);
$existing      = getAttendanceForDate($db, $selectedDate);
$statuses      = ['present', 'absent', 'late', 'excused'];
?>

<form method="post" action="index.php?action=save_attendance">

    <div class="filter-bar">
        <div class="form-group" style="margin-bottom:0">
            <label>Date</label>
            <input type="date" name="date" value="<?= esc($selectedDate) ?>" max="<?= $today ?>"
                   onchange="this.form.submit()">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label>Filter by Class</label>
            <select onchange="window.location='index.php?action=take_attendance&class='+this.value+'&date=<?= urlencode($selectedDate) ?>'">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= esc($c) ?>" <?= $filterClass === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:flex-end">
            <button type="button" class="btn btn-ghost btn-sm" onclick="markAll('present')">✅ All Present</button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="markAll('absent')">❌ All Absent</button>
        </div>
    </div>

    <?php if (empty($allStudents)): ?>
    <div class="empty">
        <div class="empty-icon">👤</div>
        <p>No students found.</p>
        <a href="index.php?action=students" class="btn btn-primary" style="margin-top:14px">Add Students</a>
    </div>
    <?php else: ?>

    <div class="table-wrap" style="margin-bottom:20px">
        <div class="att-grid">
            <div class="att-header">
                <div style="padding:10px 16px">Student</div>
                <?php foreach ($statuses as $st): ?>
                <div style="padding:10px 8px;text-align:center"><?= ucfirst($st) ?></div>
                <?php endforeach; ?>
                <div style="padding:10px 16px">Note</div>
            </div>

            <?php
            $lastClass = '';
            foreach ($allStudents as $s):
                $sid = $s['id'];
                $cur  = $existing[$sid]['status'] ?? 'present';
                $note = $existing[$sid]['note']   ?? '';
                if ($s['class'] !== $lastClass):
                    $lastClass = $s['class'];
            ?>
            <div class="att-class-divider"><?= esc($s['class']) ?></div>
            <?php endif; ?>

            <div class="att-row" data-sid="<?= $sid ?>">
                <div class="att-cell">
                    <div class="att-name"><?= esc($s['name']) ?></div>
                    <div class="att-sid"><?= esc($s['student_id']) ?></div>
                </div>
                <?php foreach ($statuses as $st): ?>
                <div class="att-radio-cell">
                    <label class="att-radio-label att-<?= $st ?>">
                        <input type="radio" name="attendance[<?= $sid ?>]" value="<?= $st ?>" <?= $cur === $st ? 'checked' : '' ?>>
                        <span><?= ucfirst($st) ?></span>
                    </label>
                </div>
                <?php endforeach; ?>
                <div class="att-note-cell">
                    <input type="text" name="notes[<?= $sid ?>]" value="<?= esc($note) ?>" placeholder="Optional note…">
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">💾&nbsp; Save Attendance</button>
        <a href="index.php?action=dashboard" class="btn btn-ghost">Cancel</a>
    </div>

    <?php endif; ?>
</form>

<script>
function markAll(status) {
    document.querySelectorAll('input[type=radio][value=' + status + ']')
        .forEach(r => r.checked = true);
}
</script>
