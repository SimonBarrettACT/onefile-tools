<?php

if (!function_exists(sort_scheduled_date)) {
    
  function sort_scheduled_date($a,$b)
    {
      $dateA = $a['ScheduledFor'];
      $dateB = $b['ScheduledFor'];

      $datetime1 = new DateTime($dateA);
      $datetime2 = new DateTime($dateB);

      if ($datetime1==$datetime2) return 0;
      return ($datetime1<$datetime2)?-1:1;

    }

}