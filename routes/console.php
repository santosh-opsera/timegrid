<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('root:report')->dailyAt(config('root.report.time', '20:00'));

Schedule::command('business:report')->dailyAt('21:00');

Schedule::command('business:vacancies')->weekly()->sundays()->at('00:00');

Schedule::command('ical:sync')->twiceDaily(0, 12);
