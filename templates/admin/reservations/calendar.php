<?php
// Navigation mois
$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear  = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear  = $month === 12 ? $year + 1 : $year;

$monthNames = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
];

// Index par date
$byDate = [];
foreach ($reservations as $r) {
    $cur = clone $r->getStartDate();
    while ($cur <= $r->getEndDate()) {
        $byDate[$cur->format('Y-m-d')][] = $r;
        $cur = $cur->modify('+1 day');
    }
}

$firstDay   = (int) $start->format('N'); // 1=lundi, 7=dimanche
$daysInMonth = (int) $end->format('j');
?>

<div class="flex items-center justify-between mb-6">
    <a href="/admin/reservations/calendrier?month=<?= $prevMonth ?>&year=<?= $prevYear ?>"
       class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
        ← <?= $monthNames[$prevMonth] ?>
    </a>
    <h2 class="text-lg font-semibold text-gray-800">
        <?= $monthNames[$month] ?> <?= $year ?>
    </h2>
    <a href="/admin/reservations/calendrier?month=<?= $nextMonth ?>&year=<?= $nextYear ?>"
       class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
        <?= $monthNames[$nextMonth] ?> →
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="grid grid-cols-7 text-center text-xs font-semibold text-gray-500 uppercase border-b">
        <?php foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $d): ?>
            <div class="py-3"><?= $d ?></div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-7">
        <?php for ($i = 1; $i < $firstDay; $i++): ?>
            <div class="bg-gray-50 border-b border-r border-gray-100 min-h-[80px]"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $daysInMonth; $day++):
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayReservations = $byDate[$dateStr] ?? [];
            $isToday = $dateStr === date('Y-m-d');
        ?>
            <div class="border-b border-r border-gray-100 p-1 min-h-[80px]
                <?= $isToday ? 'bg-blue-50' : '' ?>">
                <span class="text-xs font-medium <?= $isToday ? 'text-blue-700' : 'text-gray-500' ?>">
                    <?= $day ?>
                </span>
                <?php foreach ($dayReservations as $r): ?>
                    <a href="/admin/reservations/<?= $r->getId() ?>"
                       class="block mt-0.5 px-1 py-0.5 rounded text-xs truncate
                           <?= $r->getStatus() === 'confirmed'
                               ? 'bg-green-100 text-green-800'
                               : 'bg-yellow-100 text-yellow-800' ?>">
                        <?= e($r->getCustomerName()) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
