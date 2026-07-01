<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsToExcel
{
    protected function xlsResponse(
        string $title,
        array $columns,
        iterable $rows,
        string $filename
    ): StreamedResponse {
        $date      = now()->format('d M Y, H:i');
        $count     = is_countable($rows) ? count($rows) : 0;
        $colCount  = count($columns);
        $appName   = config('app.name', 'School Management');

        return response()->streamDownload(function () use ($title, $columns, $rows, $date, $count, $colCount, $appName) {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            ?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]>
<xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name><?= htmlspecialchars($title) ?></x:Name>
<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml>
<![endif]-->
<style>
  body  { font-family: Arial, sans-serif; font-size: 11pt; }
  .meta { margin-bottom: 16px; }
  .meta .app   { font-size: 14pt; font-weight: bold; color: #1e293b; }
  .meta .rpt   { font-size: 12pt; color: #3b82f6; font-weight: bold; }
  .meta .sub   { font-size: 9pt; color: #64748b; }
  table { border-collapse: collapse; width: 100%; }
  th {
    background-color: #1d4ed8;
    color: #ffffff;
    font-weight: bold;
    padding: 8px 12px;
    text-align: left;
    border: 1px solid #1e40af;
    white-space: nowrap;
  }
  td {
    padding: 7px 12px;
    border: 1px solid #e2e8f0;
    vertical-align: top;
  }
  tr:nth-child(even) td { background-color: #f1f5f9; }
  tr:nth-child(odd)  td { background-color: #ffffff; }
  tr:hover td { background-color: #dbeafe; }
  .footer { margin-top: 14px; font-size: 9pt; color: #94a3b8; }
</style>
</head>
<body>
<div class="meta">
  <div class="app"><?= htmlspecialchars($appName) ?></div>
  <div class="rpt"><?= htmlspecialchars($title) ?></div>
  <div class="sub">Generated: <?= $date ?> &nbsp;·&nbsp; <?= $count ?> record<?= $count !== 1 ? 's' : '' ?></div>
</div>
<table>
  <thead>
    <tr>
<?php foreach ($columns as $col): ?>
      <th><?= htmlspecialchars($col) ?></th>
<?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
<?php
            $i = 0;
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    $val = $cell === null || $cell === '' ? '&mdash;' : htmlspecialchars((string) $cell);
                    echo "<td>{$val}</td>";
                }
                echo '</tr>' . "\n";
                $i++;
            }
?>
  </tbody>
</table>
<div class="footer"><?= htmlspecialchars($appName) ?> &copy; <?= now()->year ?></div>
</body>
</html>
<?php
        }, $filename . '_' . now()->format('Y-m-d') . '.xls', [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '_' . now()->format('Y-m-d') . '.xls"',
        ]);
    }
}
