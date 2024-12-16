<?php

declare(strict_types=1);

function generateSchedule(int|null $month = null, int|null $year = null, int $period = 1): void
{
  $schedule = [];
  $data = calcStatingData($month, $year, $period);

  startGenerator($data, $schedule);

  print_r($schedule);
}

/**
 * Calculates starting data for generating a schedule.
 *
 * This function checks and defaults the provided month and year values,
 * then calculates the start and end dates based on the period. It returns
 * an array containing the start date, end date, and the number of days
 * in the specified period.
 *
 * @param string|null $month The month to start from, defaults to current month if null.
 * @param string|null $year The year to start from, defaults to current year if null.
 * @param string|null $period The number of months for the period, defaults to "1".
 * @return array An associative array with keys 'start', 'end', and 'numberDays'.
 */
function calcStatingData(int|null $month, int|null $year, int|null $period): array
{
  if ($month)
    checkValue($month);
  if ($year)
    checkValue($year);

  $month ??= date("m");
  $year ??= date("Y");

  $start = new DateTimeImmutable("$year-$month");
  $end = $start->add(new DateInterval("P{$period}M"));
  $numberDays = $start->diff($end)->days;

  return [
    "start" => $start,
    "end" => $end,
    "numberDays" => $numberDays,
  ];
}


/**
 * Checks if a given value is an integer and exits the script if it is not.
 *
 * @param mixed $value The value to check.
 * @throws TypeError If $value is not an integer.
 */
function checkValue(mixed $value): void
{
  if (!is_int($value)) {
    showError($value);
    exit;
  }
}

/**
 * Prints an error message to the console and exits the script.
 *
 * @param mixed $value The invalid value that caused the error.
 * @throws TypeError Always thrown.
 */
function showError(mixed $value): void
{
  echo "Error: $value - не является целым числом" . PHP_EOL;
}

/**
 * Starts generating a schedule based on the provided parameters.
 *
 * This function iterates over the specified number of days and calls
 * addDays() for each day. It uses the provided array $arr for
 * configuration and the array $schedule for storing the generated
 * schedule.
 *
 * @param array $arr An associative array with keys 'start', 'end', and
 *   'numberDays'.
 * @param array $schedule An array that will be populated with the
 *   generated schedule.
 */
function startGenerator(array $arr, array &$schedule): void
{
  for ($i = 1; $i <= $arr["numberDays"];) {
    addDays($i, $schedule, $arr);
  }
}

/**
 * Adds days to the schedule in the following order: off, work, off, off.
 *
 * This function adds days to the schedule in the following order: off, work, off, off.
 * It uses the provided array $arr for configuration and the array $schedule for
 * storing the generated schedule.
 *
 * @param int $i A pointer to the current day being processed.
 * @param array $schedule An array that will be populated with the generated schedule.
 * @param array $arr An associative array with keys 'start', 'end', and 'numberDays'.
 */
function addDays(int &$i, array &$schedule, array &$arr): void
{
  while (isDayOff($i, $arr['start'])) {
    addDayOff($i, $schedule, $arr);
  }

  addWorkDay($i, $schedule, $arr);
  addTwoDaysOff($i, $schedule, $arr);
}

/**
 * Adds a day off to the schedule.
 *
 * This function adds a day off to the schedule by calling calcDate() with
 * the current day and the provided array $arr for configuration. It then
 * increments the day counter $i.
 *
 * @param int $i A pointer to the current day being processed.
 * @param array $schedule An array that will be populated with the generated schedule.
 * @param array $arr An associative array with keys 'start', 'end', and 'numberDays'.
 */
function addDayOff(int &$i, array &$schedule, array &$arr): void
{
  if (isMoreDay($i, $arr['numberDays'])) return;

  $schedule[] = calcDate($i, $arr);
  $i++;
}

/**
 * Adds two days off to the schedule.
 *
 * This function adds two days off to the schedule by calling addDayOff() twice
 * with the current day and the provided array $arr for configuration.
 *
 * @param int $i A pointer to the current day being processed.
 * @param array $schedule An array that will be populated with the generated schedule.
 * @param array $arr An associative array with keys 'start', 'end', and 'numberDays'.
 */
function addTwoDaysOff(int &$i, array &$schedule, array &$arr): void
{
  addDayOff($i, $schedule, $arr);
  addDayOff($i, $schedule, $arr);
}

/**
 * Adds a work day to the schedule.
 *
 * This function adds a work day to the schedule by calling calcDate() with
 * the current day and the provided array $arr for configuration. It then
 * increments the day counter $i.
 *
 * @param int $i A pointer to the current day being processed.
 * @param array $schedule An array that will be populated with the generated schedule.
 * @param array $arr An associative array with keys 'start', 'end', and 'numberDays'.
 */
function addWorkDay(int &$i, array &$schedule, array &$arr): void
{
  if (isMoreDay($i, $arr['numberDays'])) return;

  $schedule[] = calcDate($i, $arr) . '+';
  $i++;
}

/**
 * Checks if the day at the given offset is a day off.
 *
 * This function takes a day offset $i and a date $date, and returns true
 * if the day at that offset is a Saturday or Sunday, and false otherwise.
 *
 * @param int $i The day offset to check.
 * @param DateTimeImmutable $date The date to check from.
 * @return bool True if the day at the given offset is a day off, false otherwise.
 */
function isDayOff(int $i, DateTimeImmutable $date): bool
{
  $day = $date->add(new DateInterval("P{$i}D"))->format("N");
  return $day === '6' || $day === '7';
}

/**
 * Checks if the given day offset is greater than the given number of days.
 *
 * This function takes a day offset $i and a number of days $numberDays, and
 * returns true if $i is greater than $numberDays, and false otherwise.
 *
 * @param int $i The day offset to check.
 * @param int $numberDays The number of days to check against.
 * @return bool True if $i is greater than $numberDays, false otherwise.
 */
function isMoreDay(int $i, int $numberDays): bool
{
  return $i > $numberDays;
}

/**
 * Calculates a date given a day offset and an array of configuration data.
 *
 * This function takes a day offset $i and an array $arr containing the
 * start date of the period, and returns a string representing the date
 * $i days after the start date in the format "d-m-Y".
 *
 * @param int $i The day offset to calculate the date for.
 * @param array $arr An associative array with keys 'start', 'end', and 'numberDays'.
 * @return string A string representing the calculated date in the format "d-m-Y".
 */
function calcDate(int $i, array $arr): string
{
  return $arr['start']->add(new DateInterval("P{$i}D"))->format("d-m-Y");
}

// ========== start generator ==========
generateSchedule(1, 2023, 2);
