<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Timetable;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    // Monday → Friday
    private array $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    // 12 slots per day: 9 class periods + 3 breaks
    //  [start,  end,    type,   title]
    private array $slots = [
        ['07:00', '08:40', 'class', null],
        ['08:40', '09:00', 'break', 'Morning Break'],
        ['09:00', '11:30', 'class', null],
        ['11:30', '13:00', 'break', 'Lunch Break'],
        ['13:00', '14:40', 'class', null],
        ['14:40', '15:00', 'break', 'Afternoon Break'],
        ['15:00', '16:40', 'class', null],
    ];

    // subject_id → teacher_id  (matched to TeacherSeeder specializations)
    private array $subjectTeacher = [
        1  => 1,   // Mathematics      → Sok Vanna
        2  => 2,   // English          → Sovann Ratha
        3  => 4,   // Khmer            → Srey Mony
        4  => 3,   // Science          → Dara Chan
        5  => 5,   // Social Studies   → Piseth Kosal
        6  => 6,   // Computer         → Nita Sopheak
        7  => 11,  // History          → Rithy Sina
        8  => 12,  // Geography        → Kanha Ly
        9  => 7,   // Physics          → Bunthan Sokha
        10 => 8,   // Chemistry        → Lina Chann
        11 => 9,   // Biology          → Vuthy Thida
        12 => 10,  // Physical Ed.     → Sophea Narin
    ];

    // 9 subjects per grade group (one per class period)
    private function subjectsForGrade(int $grade): array
    {
        return match (true) {
            $grade <= 8  => [1, 2, 3, 4, 5, 6, 7, 8, 12], // +PE, no Physics/Chem/Bio
            $grade <= 10 => [1, 2, 3, 4, 5, 6, 7, 8, 9],  // +Physics, no PE
            default      => [1, 2, 3, 9, 10, 11, 6, 7, 8], // Physics, Chem, Bio; no PE/Science/Social
        };
    }

    public function run(): void
    {
        Timetable::truncate();

        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            $grade    = (int) $class->name;
            $subjects = $this->subjectsForGrade($grade);

            // Each section starts at a different base offset → distinct daily orders
            $sectionShift = match ($class->section) {
                'A'     => 0,
                'B'     => 3,
                'C'     => 6,
                default => 0,
            };

            foreach ($this->days as $dayIdx => $day) {
                // Rotate subject order per day so each day has a different sequence
                $offset      = ($sectionShift + $dayIdx * 2) % count($subjects);
                $daySubjects = array_merge(
                    array_slice($subjects, $offset),
                    array_slice($subjects, 0, $offset)
                );

                $subjectCursor = 0;

                foreach ($this->slots as [$start, $end, $type, $title]) {
                    if ($type === 'break') {
                        Timetable::create([
                            'class_id'   => $class->id,
                            'entry_type' => 'break',
                            'title'      => $title,
                            'day'        => $day,
                            'start_time' => $start,
                            'end_time'   => $end,
                        ]);
                    } else {
                        $subjectId = $daySubjects[$subjectCursor++];
                        Timetable::create([
                            'class_id'   => $class->id,
                            'entry_type' => 'class',
                            'subject_id' => $subjectId,
                            'teacher_id' => $this->subjectTeacher[$subjectId],
                            'day'        => $day,
                            'start_time' => $start,
                            'end_time'   => $end,
                        ]);
                    }
                }
            }
        }

        $total   = Timetable::count();
        $classes = $classes->count();
        $this->command->info("Timetable seeded: {$total} entries across {$classes} classes.");
        $this->command->info('  · 9 class periods + 3 breaks per day (Mon–Fri)');
        $this->command->info('  · Grades 7–8: Math, English, Khmer, Science, Social, Computer, History, Geography, PE');
        $this->command->info('  · Grades 9–10: same + Physics (no PE)');
        $this->command->info('  · Grades 11–12: Math, English, Khmer, Physics, Chemistry, Biology, Computer, History, Geography');
    }
}
