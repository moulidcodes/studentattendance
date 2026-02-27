<?php
// pages/reports.php

$allStudents     = getStudents($db);
$selectedStudent = (int)($_GET['student'] ?? 0);
$classes         = getClasses($db);
?>

<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:28px;align-items:flex-end">
    <div class="form-group" style="margin-bottom:0;min-width:260px">
        <label>Select Student</label>
        <select onchange="window.location='index.php?action=reports&student='+this.value">
            <option value="0">— All Students Overview —</option>
            <?php foreach ($allStudents as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $selectedStudent === $s['id'] ? 'selected' : '' ?>>
                <?= esc($s['name']) ?> (<?= esc($s['student_id']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if ($selectedStudent > 0):
    $student = getStudentById($db, $selectedStudent);
    if (!$student): ?>
        <div class="empty"><div class="empty-icon">🔍</div><p>Student not found.</p></div>
    <?php else:
        $stats   = getStudentStats($db, $selectedStudent);
        $total   = array_sum($stats);
        $pct     = $total > 0 ? round($stats['present'] / $total * 100) : 0;
        $history = getStudentHistory($db, $selectedStudent);
        $barColor = $pct >= 80 ? 'var(--green)' : ($pct >= 60 ? 'var(--yellow)' : 'var(--red)');
    ?>

    <div class="cards" style="margin-bottom:28px">
        <div class="card">
            <div class="card-label">Student</div>
            <div style="font-size:1.15rem;font-weight:600;margin-bottom:4px"><?= esc($student['name']) ?></div>
            <div style="font-size:.8rem;color:var(--muted)"><?= esc($student['student_id']) ?> · <?= esc($student['class']) ?></div>
        </div>
        <div class="card c-blue">
            <div class="stat-icon">📅</div>
            <div class="card-label">Total Days</div>
            <div class="card-value"><?= $total ?></div>
        </div>
        <div class="card c-green">
            <div class="stat-icon">✅</div>
            <div class="card-label">Present</div>
            <div class="card-value"><?= $stats['present'] ?></div>
            <div class="card-sub"><?= $pct ?>% rate</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>"></div></div>
        </div>
        <div class="card c-red">
            <div class="stat-icon">❌</div>
            <div class="card-label">Absent</div>
            <div class="card-value"><?= $stats['absent'] ?></div>
        </div>
        <div class="card c-yellow">
            <div class="stat-icon">⏰</div>
            <div class="card-label">Late</div>
            <div class="card-value"><?= $stats['late'] ?></div>
        </div>
        <div class="card">
            <div class="stat-icon">📝</div>
            <div class="card-label">Excused</div>
            <div class="card-value" style="color:var(--blue)"><?= $stats['excused'] ?></div>
        </div>
    </div>

    <div class="section-title" style="margin-bottom:14px">Attendance History</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Status</th><th>Note</th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?>
                <tr><td colspan="3" style="padding:24px;text-align:center;color:var(--muted)">No records yet.</td></tr>
                <?php else: ?>
                <?php foreach ($history as $row): ?>
                <tr>
                    <td style="font-size:.85rem"><?= esc($row['date']) ?></td>
                    <td><span class="badge <?= badgeClass($row['status']) ?>"><?= ucfirst(esc($row['status'])) ?></span></td>
                    <td style="color:var(--muted);font-size:.8rem"><?= esc($row['note']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>

<?php else: // All students overview
    $summary = getAllStudentsSummary($db);
?>

<div class="section-title" style="margin-bottom:14px">All Students — Attendance Summary</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Class</th>
                <th>Days</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Excused</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($summary)): ?>
            <tr><td colspan="8" class="empty"><div class="empty-icon">📋</div><p>No data yet.</p></td></tr>
            <?php else: ?>
            <?php foreach ($summary as $s):
                $barColor = $s['pct'] >= 80 ? 'var(--green)' : ($s['pct'] >= 60 ? 'var(--yellow)' : 'var(--red)');
            ?>
            <tr>
                <td>
                    <a href="index.php?action=reports&student=<?= $s['id'] ?>"
                       style="color:var(--text);text-decoration:none;font-weight:500">
                        <?= esc($s['name']) ?>
                    </a>
                    <div style="font-size:.75rem;color:var(--muted)"><?= esc($s['student_id']) ?></div>
                </td>
                <td style="color:var(--muted)"><?= esc($s['class']) ?></td>
                <td><?= $s['total'] ?></td>
                <td style="color:var(--green)"><?= $s['stats']['present'] ?></td>
                <td style="color:var(--red)"><?= $s['stats']['absent'] ?></td>
                <td style="color:var(--yellow)"><?= $s['stats']['late'] ?></td>
                <td style="color:var(--blue)"><?= $s['stats']['excused'] ?></td>
                <td style="min-width:120px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div class="progress" style="flex:1">
                            <div class="progress-bar" style="width:<?= $s['pct'] ?>%;background:<?= $barColor ?>"></div>
                        </div>
                        <span style="font-size:.8rem;color:var(--muted)"><?= $s['pct'] ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>
