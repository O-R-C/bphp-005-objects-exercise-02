<?php

declare(strict_types=1);

function generateSchedule(string|null $month = null, string|null $year = null, string $period = "1"): void
{
  $month ??= date('m');
  $year ??= date('Y');

  $start = new DateTimeImmutable("$year-$month");
  $end = $start->add(new DateInterval("P{$period}M"));
  $numberDays = $start->diff($end)->days;

  startGenerator($start, $numberDays);
}

function startGenerator(DateTimeImmutable $start, int $numberDays): void
{

  echo $numberDays . $start->format(" d-m.Y") . PHP_EOL;
}

// ========== start generator ==========
generateSchedule();
