<?php

declare(strict_types=1);

use Rore\ValueObject\DateRange;

class DateRangeTest
{
    // ── nbDays ────────────────────────────────────────────────────────────

    public function testSameDayIsOneDay(): void
    {
        $r = new DateRange('2026-06-01', '2026-06-01');
        Assert::equals(1, $r->nbDays());
    }

    public function testTwoDays(): void
    {
        $r = new DateRange('2026-06-01', '2026-06-02');
        Assert::equals(2, $r->nbDays());
    }

    public function testWeekend(): void
    {
        $r = new DateRange('2026-06-06', '2026-06-07');
        Assert::equals(2, $r->nbDays());
    }

    public function testFullWeek(): void
    {
        $r = new DateRange('2026-06-01', '2026-06-07');
        Assert::equals(7, $r->nbDays());
    }

    public function testCrossMonth(): void
    {
        $r = new DateRange('2026-05-30', '2026-06-01');
        Assert::equals(3, $r->nbDays());
    }

    // ── label ─────────────────────────────────────────────────────────────

    public function testLabelSameMonth(): void
    {
        $r = new DateRange('2026-06-12', '2026-06-14');
        Assert::equals('du 12 juin au 14 juin 2026', $r->label());
    }

    public function testLabelCrossYear(): void
    {
        $r = new DateRange('2025-12-30', '2026-01-02');
        Assert::equals('du 30 décembre 2025 au 2 janvier 2026', $r->label());
    }

    public function testLabelSameDay(): void
    {
        $r = new DateRange('2026-03-01', '2026-03-01');
        Assert::equals('du 1 mars au 1 mars 2026', $r->label());
    }

    // ── Accepte DateTimeImmutable ─────────────────────────────────────────

    public function testAcceptsDateTimeImmutable(): void
    {
        $r = new DateRange(
            new \DateTimeImmutable('2026-06-01'),
            new \DateTimeImmutable('2026-06-03'),
        );
        Assert::equals(3, $r->nbDays());
    }

    // ── Propriétés readonly ───────────────────────────────────────────────

    public function testStartEndReadable(): void
    {
        $r = new DateRange('2026-06-01', '2026-06-05');
        Assert::equals('2026-06-01', $r->start->format('Y-m-d'));
        Assert::equals('2026-06-05', $r->end->format('Y-m-d'));
    }
}
