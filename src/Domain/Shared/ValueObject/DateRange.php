<?php

declare(strict_types=1);

namespace Rore\Domain\Shared\ValueObject;

use Rore\Framework\Type\Castable;

/**
 * Intervalle de dates inclusif (granularité journalière).
 */
final class DateRange
{
    use Castable;
    public readonly \DateTimeImmutable $start;
    public readonly \DateTimeImmutable $end;

    public function __construct(
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end,
    ) {
        $this->start = is_string($start) ? new \DateTimeImmutable($start) : \DateTimeImmutable::createFromInterface($start);
        $this->end   = is_string($end)   ? new \DateTimeImmutable($end)   : \DateTimeImmutable::createFromInterface($end);
    }

    /**
     * Nombre de jours dans l'intervalle (inclusif).
     */
    public function nbDays(): int
    {
        return (int) $this->start->diff($this->end)->days + 1;
    }

    /**
     * Libellé lisible en français.
     * Ex : "du 12 juin au 14 juin 2026"
     */
    public function label(): string
    {
        $months = [
            1 => 'janvier', 2 => 'février',  3 => 'mars',      4 => 'avril',
            5 => 'mai',     6 => 'juin',      7 => 'juillet',   8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];

        $startStr = $this->start->format('j') . ' ' . $months[(int) $this->start->format('n')];
        $endStr   = $this->end->format('j')   . ' ' . $months[(int) $this->end->format('n')]
                  . ' ' . $this->end->format('Y');

        if ($this->start->format('Y') !== $this->end->format('Y')) {
            $startStr .= ' ' . $this->start->format('Y');
        }

        return "du $startStr au $endStr";
    }
}
