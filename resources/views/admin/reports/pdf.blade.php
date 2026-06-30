<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

    .header { text-align: center; padding: 14px 0 10px; border-bottom: 2px solid #1e293b; margin-bottom: 12px; }
    .header h1 { font-size: 16px; font-weight: 700; }
    .header p  { font-size: 10px; color: #64748b; margin-top: 3px; }

    .meta { display: flex; gap: 24px; margin-bottom: 12px; }
    .meta-item label { font-size: 8px; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; display: block; }
    .meta-item span  { font-size: 11px; font-weight: 700; color: #1e293b; }

    table { width: 100%; border-collapse: collapse; }
    thead tr th {
        background: #1e293b; color: #fff;
        padding: 5px 6px; font-size: 8px;
        text-transform: uppercase; letter-spacing: .04em;
        text-align: center;
    }
    thead tr th:nth-child(2) { text-align: left; }
    thead tr th.dim { background: #334155; }

    tbody tr td { padding: 4px 6px; font-size: 9px; border-bottom: 1px solid #f1f5f9; text-align: center; }
    tbody tr td:nth-child(2) { text-align: left; }
    tbody tr:nth-child(even) td { background: #f8fafc; }

    .rank-col  { width: 28px; font-weight: 700; color: #64748b; }
    .name-col  { min-width: 110px; font-weight: 600; }
    .total-col { background: #f1f5f9 !important; font-weight: 700; }
    .pct-col   { background: #f1f5f9 !important; }
    .grade-col { background: #f1f5f9 !important; font-weight: 700; }

    .grade-badge { padding: 1px 5px; border-radius: 999px; font-size: 8px; font-weight: 700; }

    tfoot tr td { background: #f8fafc; font-weight: 700; font-size: 9px; padding: 5px 6px;
                  border-top: 2px solid #cbd5e1; text-align: center; }
    tfoot tr td:nth-child(2) { text-align: left; color: #64748b; font-size: 8px; text-transform: uppercase; }

    .legend { margin-top: 10px; font-size: 8px; color: #64748b; }
    .legend span { margin-right: 10px; }
    .footer { margin-top: 8px; font-size: 8px; color: #94a3b8; text-align: right; }
</style>
</head>
<body>

<div class="header">
    <h1>{{ $class->name }} – {{ $class->section }} &nbsp;·&nbsp; {{ $examType }} Result Report</h1>
    <p>Generated on {{ now()->format('d M Y, H:i') }}</p>
</div>

<div class="meta">
    <div class="meta-item"><label>Class</label><span>{{ $class->name }} – {{ $class->section }}</span></div>
    <div class="meta-item"><label>Exam Type</label><span>{{ $examType }}</span></div>
    <div class="meta-item"><label>Subjects</label><span>{{ $exams->count() }}</span></div>
    <div class="meta-item"><label>Total Marks</label><span>{{ $grandTotal }}</span></div>
    <div class="meta-item"><label>Students</label><span>{{ $students->count() }}</span></div>
</div>

<table>
    <thead>
        <tr>
            <th class="rank-col">#</th>
            <th style="text-align:left">Student</th>
            @foreach($exams as $exam)
            <th>{{ $exam->subject->name ?? '—' }}<br><span style="font-weight:400;color:#94a3b8">/{{ $exam->total_marks }}</span></th>
            @endforeach
            <th class="dim">Total<br><span style="font-weight:400;color:#94a3b8">/{{ $grandTotal }}</span></th>
            <th class="dim">%</th>
            <th class="dim">Grade</th>
            <th class="dim">Rank</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        @php
            $obtained   = 0;
            $allEntered = true;
            $subMarks   = [];
            foreach ($exams as $exam) {
                $result = $allResults->get($student->id)?->get($exam->id);
                if ($result) {
                    $subMarks[$exam->id] = $result->marks_obtained;
                    $obtained += $result->marks_obtained;
                } else {
                    $subMarks[$exam->id] = null;
                    $allEntered = false;
                }
            }
            $pct = $grandTotal > 0 && $obtained > 0 ? ($obtained / $grandTotal) * 100 : 0;
            $gi  = $obtained > 0 ? $gradeInfo($pct) : null;
        @endphp
        <tr>
            <td class="rank-col">{{ $rankMap[$student->id] ?? '—' }}</td>
            <td class="name-col">{{ $student->first_name }} {{ $student->last_name }}</td>
            @foreach($exams as $exam)
            @php
                $m = $subMarks[$exam->id];
                $subPct = ($m !== null && $exam->total_marks > 0) ? ($m / $exam->total_marks) * 100 : 0;
                $sg = $m !== null ? $gradeInfo($subPct) : null;
            @endphp
            <td>
                @if($m !== null)
                {{ number_format($m, 0) }}
                <br><span style="font-size:7px;color:{{ $sg['color'] }};font-weight:700">{{ $sg['label'] }}</span>
                @else
                <span style="color:#cbd5e1">—</span>
                @endif
            </td>
            @endforeach
            <td class="total-col">
                @if($obtained > 0){{ number_format($obtained, 0) }}@else<span style="color:#cbd5e1">—</span>@endif
            </td>
            <td class="pct-col" style="color: {{ $obtained > 0 ? ($pct >= 50 ? '#059669' : '#dc2626') : '#cbd5e1' }}">
                @if($obtained > 0){{ number_format($pct, 1) }}%@else—@endif
            </td>
            <td class="grade-col">
                @if($gi)
                <span class="grade-badge" style="color:{{ $gi['color'] }}">{{ $gi['label'] }}{{ !$allEntered ? '*' : '' }}</span>
                @else
                <span style="color:#cbd5e1">—</span>
                @endif
            </td>
            <td class="grade-col" style="color:#64748b">
                @if($obtained > 0)#{{ $rankMap[$student->id] ?? '—' }}@else—@endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td>Class Average</td>
            @foreach($exams as $exam)
            @php
                $avg = \App\Models\ExamResult::where('exam_id', $exam->id)->avg('marks_obtained');
            @endphp
            <td>{{ $avg !== null ? number_format($avg, 1) : '—' }}</td>
            @endforeach
            <td colspan="4"></td>
        </tr>
    </tfoot>
</table>

<div class="legend">
    <strong>Grade: </strong>
    <span>A+/A ≥80%</span>
    <span>B+/B ≥60%</span>
    <span>C ≥50%</span>
    <span>D ≥40%</span>
    <span>F &lt;40%</span>
    @if($students->contains(fn($s) => !$allResults->get($s->id)?->count() || $allResults->get($s->id)?->count() < $exams->count()))
    &nbsp;· <span>* = partial results (some subjects not yet entered)</span>
    @endif
</div>

<div class="footer">{{ config('app.name') }} · Result Report</div>

</body>
</html>
