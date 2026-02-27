<?php
// pages/dashboard.php

$stats  = getDashboardStats($db);
$recent = getRecentActivity($db);
$pct    = $stats['total'] > 0 ? round($stats['present'] / $stats['total'] * 100) : 0;
?>

<div class="cards">
    <div class="card c-blue">
        <div class="stat-icon">👤</div>
        <div class="card-label">Total Students</div>
        <div class="card-value"><?= $stats['total'] ?></div>
        <div class="card-sub">Enrolled</div>
    </div>
    <div class="card c-green">
        <div class="stat-icon">✅</div>
        <div class="card-label">Present Today</div>
        <div class="card-value"><?= $stats['present'] ?></div>
        <div class="progress">
            <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--green)"></div>
        </div>
    </div>
    <div class="card c-red">
        <div class="stat-icon">❌</div>
        <div class="card-label">Absent Today</div>
        <div class="card-value"><?= $stats['absent'] ?></div>
        <div class="card-sub">As of today</div>
    </div>
    <div class="card c-yellow">
        <div class="stat-icon">⏰</div>
        <div class="card-label">Late Today</div>
        <div class="card-value"><?= $stats['late'] ?></div>
        <div class="card-sub">Tardiness</div>
    </div>
    <div class="card">
        <div class="stat-icon">📅</div>
        <div class="card-label">Days Recorded</div>
        <div class="card-value"><?= $stats['days'] ?></div>
        <div class="card-sub">School days tracked</div>
    </div>
</div>

<div class="section-header">
    <div class="section-title">Recent Activity</div>
    <a href="index.php?action=reports" class="btn btn-ghost btn-sm">View All Reports →</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>ID</th>
                <th>Status</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent)): ?>
            <tr>
                <td colspan="5" style="padding:32px;text-align:center;color:var(--muted)">
                    No attendance records yet.
                    <a href="index.php?action=take_attendance" style="color:var(--accent)">Take attendance →</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($recent as $row): ?>
            <tr>
                <td style="color:var(--muted);font-size:.8rem"><?= esc($row['date']) ?></td>
                <td style="font-weight:500"><?= esc($row['name']) ?></td>
                <td style="color:var(--muted);font-family:monospace;font-size:.82rem"><?= esc($row['sid']) ?></td>
                <td><span class="badge <?= badgeClass($row['status']) ?>"><?= ucfirst(esc($row['status'])) ?></span></td>
                <td style="color:var(--muted);font-size:.8rem"><?= esc($row['note']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
