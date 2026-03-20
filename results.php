<?php
$file    = __DIR__ . '/results.json';
$results = [];

if (file_exists($file)) {
    $contents = file_get_contents($file);
    $results  = json_decode($contents, true) ?? [];
}

$total_entries = count($results);
$avg_score     = $total_entries > 0
    ? round(array_sum(array_column($results, 'score')) / $total_entries, 1)
    : 0;
$highest       = $total_entries > 0 ? max(array_column($results, 'score')) : 0;
$passing       = count(array_filter($results, fn($r) => ($r['score'] / $r['total']) >= 0.60));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Examination Results · Accompro</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --navy:       #0d1b2a;
  --navy-mid:   #162535;
  --navy-light: #1e3348;
  --gold:       #c9a84c;
  --gold-light: #e2c97e;
  --gold-pale:  #f5ecd4;
  --cream:      #faf7f0;
  --text:       #1a1a2e;
  --text-muted: #5a5a72;
  --border:     #d4c5a0;
  --white:      #ffffff;
  --pass:       #2e7d4f;
  --pass-bg:    #edf7f1;
  --fail:       #8b1a1a;
  --fail-bg:    #fdf0f0;
  --shadow-lg:  0 16px 56px rgba(13,27,42,.18);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Crimson Pro', Georgia, serif;
  background: var(--cream);
  background-image:
    radial-gradient(ellipse at 15% 0%,   rgba(201,168,76,.10) 0%, transparent 50%),
    radial-gradient(ellipse at 85% 100%, rgba(13,27,42,.06)   0%, transparent 50%);
  min-height: 100vh;
  padding: 32px 20px 64px;
  color: var(--text);
}

.container {
  max-width: 1100px;
  margin: auto;
  background: var(--white);
  border-radius: 2px;
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  border: 1px solid var(--border);
}

/* ── HEADER ── */
.page-header {
  background: var(--navy);
  position: relative;
  overflow: hidden;
}

.page-header::before {
  content: '';
  position: absolute; inset: 0;
  background:
    repeating-linear-gradient(90deg, transparent, transparent 60px, rgba(201,168,76,.04) 60px, rgba(201,168,76,.04) 61px),
    repeating-linear-gradient(0deg,  transparent, transparent 60px, rgba(201,168,76,.04) 60px, rgba(201,168,76,.04) 61px);
  pointer-events: none;
}

.header-stripe {
  height: 4px;
  background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
}

.header-inner {
  padding: 32px 48px 28px;
  position: relative; z-index: 1;
  display: flex;
  align-items: center;
  gap: 28px;
}

.seal {
  width: 64px; height: 64px;
  flex-shrink: 0;
  border: 2px solid var(--gold);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  color: var(--gold);
  background: rgba(201,168,76,.08);
}

.header-text { flex: 1; }

.header-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px; letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--gold); opacity: .8;
  margin-bottom: 7px;
}

.header-title {
  font-family: 'Playfair Display', serif;
  font-size: 26px; font-weight: 700;
  color: var(--white); line-height: 1.2;
}

.header-sub {
  margin-top: 5px;
  font-size: 15px;
  color: rgba(255,255,255,.45);
  font-style: italic;
}

.header-actions {
  display: flex; gap: 10px; align-items: center;
}

.btn-print {
  padding: 9px 22px;
  border: 1.5px solid rgba(201,168,76,.5);
  border-radius: 2px;
  background: transparent;
  color: var(--gold-light);
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px; letter-spacing: 1.5px;
  text-transform: uppercase;
  cursor: pointer;
  text-decoration: none;
  transition: background .2s, border-color .2s;
  display: inline-flex; align-items: center; gap: 7px;
}

.btn-print:hover {
  background: rgba(201,168,76,.1);
  border-color: var(--gold);
}

.btn-clear {
  padding: 9px 22px;
  border: 1.5px solid rgba(220,80,80,.4);
  border-radius: 2px;
  background: transparent;
  color: #e07070;
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px; letter-spacing: 1.5px;
  text-transform: uppercase;
  cursor: pointer;
  text-decoration: none;
  transition: background .2s, border-color .2s;
  display: inline-flex; align-items: center; gap: 7px;
}

.btn-clear:hover {
  background: rgba(220,80,80,.08);
  border-color: #e07070;
}

/* ── STATS ROW ── */
.stats-bar {
  background: var(--navy-mid);
  border-top: 1px solid rgba(201,168,76,.15);
  padding: 18px 48px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
}

.stat-item {
  text-align: center;
  padding: 0 20px;
  border-right: 1px solid rgba(255,255,255,.07);
}

.stat-item:last-child { border-right: none; }

.stat-value {
  font-family: 'Playfair Display', serif;
  font-size: 26px;
  font-weight: 700;
  color: var(--gold-light);
  line-height: 1;
  margin-bottom: 5px;
}

.stat-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 9.5px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(255,255,255,.35);
}

/* ── BODY ── */
.page-body { padding: 36px 48px; }

/* ── CONTROLS ROW ── */
.controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 16px;
  flex-wrap: wrap;
}

.section-heading {
  font-family: 'Playfair Display', serif;
  font-size: 11px; font-weight: 600;
  letter-spacing: 2.5px; text-transform: uppercase;
  color: var(--text-muted);
}

.search-wrap {
  display: flex; align-items: center; gap: 10px;
}

.search-input {
  padding: 8px 14px;
  border: 1px solid var(--border);
  border-radius: 2px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text);
  background: var(--cream);
  width: 220px;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
}

.search-input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201,168,76,.1);
  background: var(--white);
}

.search-input::placeholder { color: #aaa; }

/* ── TABLE ── */
.table-wrap {
  overflow-x: auto;
  border: 1px solid var(--border);
  border-radius: 2px;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
}

thead {
  background: var(--navy);
  position: sticky; top: 0; z-index: 2;
}

thead th {
  padding: 13px 18px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 9.5px; font-weight: 500;
  letter-spacing: 1.8px; text-transform: uppercase;
  color: rgba(201,168,76,.75);
  text-align: left;
  border-right: 1px solid rgba(255,255,255,.05);
  white-space: nowrap;
}

thead th:last-child { border-right: none; }

thead th.sortable { cursor: pointer; user-select: none; }
thead th.sortable:hover { color: var(--gold-light); }

tbody tr {
  border-bottom: 1px solid var(--border);
  transition: background .15s;
  animation: fadeRow .35s ease both;
}

@keyframes fadeRow {
  from { opacity: 0; transform: translateY(4px); }
  to   { opacity: 1; transform: translateY(0); }
}

tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(201,168,76,.04); }

tbody td {
  padding: 13px 18px;
  color: var(--text);
  vertical-align: middle;
  border-right: 1px solid rgba(212,197,160,.35);
}

tbody td:last-child { border-right: none; }

/* rank col */
.col-rank {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text-muted);
  text-align: center;
  width: 52px;
}

.rank-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 26px; height: 26px;
  border-radius: 50%;
  font-size: 11px; font-weight: 600;
  font-family: 'JetBrains Mono', monospace;
}

.rank-1 { background: #c9a84c22; color: var(--gold); border: 1.5px solid var(--gold); }
.rank-2 { background: #c0c0c022; color: #a0a0b0;   border: 1.5px solid #a0a0b0; }
.rank-3 { background: #cd7f3222; color: #b87340;   border: 1.5px solid #b87340; }
.rank-n { background: transparent; color: var(--text-muted); border: 1.5px solid var(--border); }

/* name col */
.col-name strong {
  display: block;
  font-family: 'Crimson Pro', serif;
  font-size: 16px; font-weight: 600;
  color: var(--navy);
  line-height: 1.2;
}

.col-name span {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10.5px;
  color: var(--text-muted);
  letter-spacing: .5px;
}

/* score col */
.col-score {
  white-space: nowrap;
}

.score-fraction {
  font-family: 'Playfair Display', serif;
  font-size: 18px; font-weight: 700;
  color: var(--navy);
}

.score-bar-wrap {
  margin-top: 5px;
  height: 4px;
  background: var(--border);
  border-radius: 2px;
  overflow: hidden;
  width: 100px;
}

.score-bar {
  height: 100%;
  border-radius: 2px;
  transition: width .5s ease;
}

/* grade badge */
.grade-badge {
  display: inline-flex; align-items: center;
  padding: 3px 12px;
  border-radius: 20px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 10.5px; font-weight: 500;
  letter-spacing: 1px; text-transform: uppercase;
  white-space: nowrap;
}

.grade-pass { background: var(--pass-bg); color: var(--pass); border: 1px solid #a8d5b8; }
.grade-fail { background: var(--fail-bg); color: var(--fail); border: 1px solid #e0b0b0; }

/* datetime */
.col-datetime {
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px; color: var(--text-muted);
  white-space: nowrap;
  line-height: 1.6;
}

/* percentage col */
.col-pct {
  font-family: 'JetBrains Mono', monospace;
  font-size: 14px; font-weight: 500;
  color: var(--navy);
}

/* ── EMPTY STATE ── */
.empty-state {
  padding: 64px 32px;
  text-align: center;
}

.empty-icon {
  font-size: 40px; margin-bottom: 16px; opacity: .35;
}

.empty-title {
  font-family: 'Playfair Display', serif;
  font-size: 20px; color: var(--text-muted);
  margin-bottom: 8px;
}

.empty-sub {
  font-size: 15px; color: #aaa; font-style: italic;
}

/* ── FOOTER ── */
.page-footer {
  background: var(--navy);
  padding: 14px 48px;
  display: flex; justify-content: space-between; align-items: center;
  border-top: 1px solid rgba(201,168,76,.12);
}

.footer-left, .footer-right {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px; letter-spacing: 1px; text-transform: uppercase;
}

.footer-left  { color: rgba(255,255,255,.22); }
.footer-right { color: rgba(201,168,76,.45); }

/* ── NO RESULTS ROW ── */
.no-results td {
  text-align: center;
  padding: 40px;
  font-style: italic;
  color: var(--text-muted);
  font-size: 15px;
}

/* ── PRINT ── */
@media print {
  body { background: white; padding: 0; }
  .container { box-shadow: none; border: none; }
  .page-header::before { display: none; }
  .btn-print, .btn-clear, .controls .search-wrap { display: none !important; }
  thead { background: #1a2a3a !important; -webkit-print-color-adjust: exact; }
  .stats-bar { background: #1e3348 !important; -webkit-print-color-adjust: exact; }
  .page-footer { background: #1a2a3a !important; -webkit-print-color-adjust: exact; }
}

@media (max-width: 768px) {
  body { padding: 10px; }
  .header-inner { padding: 22px 18px; flex-wrap: wrap; }
  .header-actions { width: 100%; justify-content: flex-start; }
  .stats-bar { grid-template-columns: 1fr 1fr; padding: 16px 18px; gap: 12px; }
  .stat-item { border-right: none; }
  .page-body { padding: 22px 16px; }
  .page-footer { padding: 12px 16px; flex-direction: column; gap: 4px; text-align: center; }
  .controls { flex-direction: column; align-items: flex-start; }
  .search-input { width: 100%; }
}
</style>
</head>
<body>
<div class="container">

  <!-- HEADER -->
  <header class="page-header">
    <div class="header-stripe"></div>
    <div class="header-inner">
      <div class="seal">ACP</div>
      <div class="header-text">
        <div class="header-label">Accompro · Academic Year 2025–2026</div>
        <h1 class="header-title">Examination Results</h1>
        <p class="header-sub">Prof. Keith Rivera &nbsp;·&nbsp; Computer Applications &nbsp;·&nbsp; Final Term</p>
      </div>
      <div class="header-actions">
        <button class="btn-print" onclick="window.print()">⎙ Print</button>
        <a class="btn-clear"
           href="?clear=1"
           onclick="return confirm('Are you sure you want to clear all results? This cannot be undone.')">
           ✕ Clear All
        </a>
      </div>
    </div>

    <!-- STATS -->
    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-value"><?= $total_entries ?></div>
        <div class="stat-label">Examinees</div>
      </div>
      <div class="stat-item">
        <div class="stat-value"><?= $avg_score ?><span style="font-size:14px;opacity:.6">/60</span></div>
        <div class="stat-label">Average Score</div>
      </div>
      <div class="stat-item">
        <div class="stat-value"><?= $highest ?><span style="font-size:14px;opacity:.6">/60</span></div>
        <div class="stat-label">Highest Score</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">
          <?= $total_entries > 0 ? round(($passing / $total_entries) * 100) : 0 ?>%
        </div>
        <div class="stat-label">Passing Rate</div>
      </div>
    </div>
  </header>

  <!-- BODY -->
  <main class="page-body">

    <div class="controls">
      <div class="section-heading">Submission Records &nbsp;<span style="font-weight:300;opacity:.5">—</span>&nbsp; <?= $total_entries ?> <?= $total_entries === 1 ? 'entry' : 'entries' ?></div>
      <div class="search-wrap">
        <input class="search-input" id="searchInput" type="text" placeholder="Search by name or section…" oninput="filterTable()">
      </div>
    </div>

    <?php if (empty($results)): ?>
    <div class="empty-state">
      <div class="empty-icon">📋</div>
      <div class="empty-title">No submissions yet</div>
      <div class="empty-sub">Results will appear here once examinees submit their exam.</div>
    </div>
    <?php else: ?>

    <div class="table-wrap">
      <table id="resultsTable">
        <thead>
          <tr>
            <th class="sortable" onclick="sortTable(0)">#</th>
            <th class="sortable" onclick="sortTable(1)">Name &amp; Section</th>
            <th class="sortable" onclick="sortTable(2)">Score</th>
            <th class="sortable" onclick="sortTable(3)">Percentage</th>
            <th>Remark</th>
            <th class="sortable" onclick="sortTable(5)">Date &amp; Time Submitted</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php
          // Sort by score descending for rank
          $ranked = $results;
          usort($ranked, fn($a, $b) => $b['score'] - $a['score']);

          // Build a rank map by name+submitted_at key
          $rankMap = [];
          foreach ($ranked as $ri => $r) {
            $key = $r['name'] . '|' . $r['submitted_at'];
            $rankMap[$key] = $ri + 1;
          }

          foreach ($results as $r):
            $pct      = round(($r['score'] / $r['total']) * 100, 1);
            $pass     = $pct >= 60;
            $key      = $r['name'] . '|' . $r['submitted_at'];
            $rank     = $rankMap[$key] ?? '—';
            $barColor = $pass ? '#4caf7a' : '#e05252';

            // Color the score bar
            if ($pct >= 85)      $barColor = '#4caf7a';
            elseif ($pct >= 70)  $barColor = '#7ec87e';
            elseif ($pct >= 60)  $barColor = '#c9a84c';
            else                 $barColor = '#e05252';

            // Rank badge class
            if     ($rank === 1) $rankClass = 'rank-1';
            elseif ($rank === 2) $rankClass = 'rank-2';
            elseif ($rank === 3) $rankClass = 'rank-3';
            else                 $rankClass = 'rank-n';

            $dt = new DateTime($r['submitted_at']);
          ?>
          <tr>
            <td class="col-rank">
              <span class="rank-badge <?= $rankClass ?>"><?= $rank ?></span>
            </td>
            <td class="col-name">
              <strong><?= htmlspecialchars($r['name']) ?></strong>
              <span><?= htmlspecialchars($r['course']) ?></span>
            </td>
            <td class="col-score">
              <div class="score-fraction"><?= $r['score'] ?><span style="font-size:13px;font-weight:400;color:var(--text-muted);">/<?= $r['total'] ?></span></div>
              <div class="score-bar-wrap">
                <div class="score-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
              </div>
            </td>
            <td class="col-pct"><?= $pct ?>%</td>
            <td>
              <?php if ($pass): ?>
                <span class="grade-badge grade-pass">✓ Passed</span>
              <?php else: ?>
                <span class="grade-badge grade-fail">✗ Failed</span>
              <?php endif; ?>
            </td>
            <td class="col-datetime">
              <?= $dt->format('M d, Y') ?><br>
              <?= $dt->format('h:i A') ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php endif; ?>

  </main>

  <!-- FOOTER -->
  <footer class="page-footer">
    <div class="footer-left">Accompro · Final Term Assessment</div>
    <div class="footer-right">Generated <?= date('M d, Y · h:i A') ?></div>
  </footer>

</div>

<script>
// ── SEARCH FILTER ──
function filterTable() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  const rows  = document.querySelectorAll('#tableBody tr');
  let visible = 0;

  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const match = text.includes(query);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });

  // Show a "no match" row if needed
  let noRow = document.getElementById('noMatchRow');
  if (visible === 0) {
    if (!noRow) {
      noRow = document.createElement('tr');
      noRow.id = 'noMatchRow';
      noRow.className = 'no-results';
      noRow.innerHTML = '<td colspan="6">No matching records found.</td>';
      document.getElementById('tableBody').appendChild(noRow);
    }
    noRow.style.display = '';
  } else if (noRow) {
    noRow.style.display = 'none';
  }
}

// ── SORT ──
let sortDir = {};

function sortTable(colIdx) {
  const tbody = document.getElementById('tableBody');
  const rows  = Array.from(tbody.querySelectorAll('tr:not(#noMatchRow)'));
  const asc   = !sortDir[colIdx];
  sortDir = {};
  sortDir[colIdx] = asc;

  rows.sort((a, b) => {
    const ta = a.cells[colIdx]?.textContent.trim() ?? '';
    const tb = b.cells[colIdx]?.textContent.trim() ?? '';
    const na = parseFloat(ta), nb = parseFloat(tb);
    if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
    return asc ? ta.localeCompare(tb) : tb.localeCompare(ta);
  });

  rows.forEach(r => tbody.appendChild(r));
}

// Staggered fade-in for rows
document.querySelectorAll('#tableBody tr').forEach((row, i) => {
  row.style.animationDelay = `${i * 40}ms`;
});
</script>

<?php
// Handle clear action
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    $file = __DIR__ . '/results.json';
    if (file_exists($file)) file_put_contents($file, json_encode([]));
    header('Location: results.php');
    exit;
}
?>
</body>
</html>
