<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Tools\CalendarTools;

class CalendarToolsTest extends TestCase
{
    private CalendarTools $calendarTools;
   
    public function testCheckSlots(): void
    {
        $result = $this->calendarTools->checkCalendarSlots('2026-10-10', '09:00', 60);
        $this->assertTrue($result);
    }
    
    
}