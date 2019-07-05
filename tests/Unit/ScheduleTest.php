<?php

namespace Ambulatory\Ambulatory\Tests\Unit;

use Illuminate\Support\Arr;
use Ambulatory\Ambulatory\Doctor;
use Ambulatory\Ambulatory\Schedule;
use Illuminate\Support\Collection;
use Ambulatory\Ambulatory\Availability;
use Ambulatory\Ambulatory\HealthFacility;
use Ambulatory\Ambulatory\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sets_the_uuid_when_creating_a_new_schedule()
    {
        $schedule = factory(Schedule::class)->create();

        $this->assertNotNull($schedule->id);
    }

    /** @test */
    public function it_belongs_to_a_health_facility()
    {
        $schedule = factory(Schedule::class)->create();

        $this->assertInstanceOf(HealthFacility::class, $schedule->healthFacility);
    }

    /** @test */
    public function it_belongs_to_a_doctor()
    {
        $schedule = factory(Schedule::class)->create();

        $this->assertInstanceOf(Doctor::class, $schedule->doctor);
    }

    /** @test */
    public function it_can_included_to_the_bookings()
    {
        $schedule = factory(Schedule::class)->create();

        $this->assertInstanceOf(Collection::class, $schedule->bookings);
    }

    /** @test */
    public function it_can_add_a_custom_availability()
    {
        $schedule = factory(Schedule::class)->create();

        $availability = $schedule->addCustomAvailability(Arr::except(factory(Availability::class)->raw(), ['schedule_id']));

        // included default working hours.
        $this->assertCount(6, $schedule->availabilities);

        $this->assertDatabaseHas('ambulatory_availabilities', [
            'intervals' => json_encode($availability['intervals']),
        ]);
    }
}
