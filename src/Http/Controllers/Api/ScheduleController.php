<?php

namespace Cachet\Http\Controllers\Api;

use Cachet\Actions\Schedule\CreateSchedule;
use Cachet\Actions\Schedule\DeleteSchedule;
use Cachet\Actions\Schedule\UpdateSchedule;
use Cachet\Concerns\GuardsApiAbilities;
use Cachet\Data\Requests\Schedule\CreateScheduleRequestData;
use Cachet\Data\Requests\Schedule\UpdateScheduleRequestData;
use Cachet\Http\Resources\Schedule as ScheduleResource;
use Cachet\Models\Schedule;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Schedules
 */
class ScheduleController extends Controller
{
    use GuardsApiAbilities;

    /**
     * List Schedules
     *
     * @apiResourceCollection \Cachet\Http\Resources\Schedule
     *
     * @apiResourceModel \Cachet\Models\Schedule
     *
     * @queryParam per_page int How many items to show per page. Example: 20
     * @queryParam page int Which page to show. Example: 2
     * @queryParam sort Field to sort by. Enum: name, id, scheduled_at, completed_at, enabled. Example: name
     * @queryParam include Include related resources. Enum: components, updates, user. Example: components
     * @queryParam filters[name] string Filter the resources by name. Example: api
     * @queryParam filters[status] string Filter the resources by status. Example: 1
     */
    public function index()
    {
        $schedules = QueryBuilder::for(Schedule::class)
            ->allowedIncludes(['components', 'updates', 'user'])
            ->allowedFilters(['name', AllowedFilter::exact('status')])
            ->allowedSorts(['name', 'id', 'scheduled_at', 'completed_at'])
            ->simplePaginate(request('per_page', 15));

        return ScheduleResource::collection($schedules);
    }

    /**
     * Create Schedule
     *
     * @apiResource \Cachet\Http\Resources\Schedule
     *
     * @apiResourceModel \Cachet\Models\Schedule
     *
     * @authenticated
     */
    public function store(CreateScheduleRequestData $data, CreateSchedule $createScheduleAction)
    {
        $this->guard('schedules.manage');

        $schedule = $createScheduleAction->handle($data);

        return ScheduleResource::make($schedule);
    }

    /**
     * Get Schedule
     *
     * @apiResource \Cachet\Http\Resources\Schedule
     *
     * @apiResourceModel \Cachet\Models\Schedule
     *
     * @queryParam include Include related resources. Enum: components, updates, user. Example: components
     */
    public function show(Schedule $schedule)
    {
        $scheduleQuery = QueryBuilder::for($schedule)
            ->allowedIncludes(['components', 'updates', 'user'])
            ->first();

        return ScheduleResource::make($scheduleQuery)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update Schedule
     *
     * @apiResource \Cachet\Http\Resources\Schedule
     *
     * @apiResourceModel \Cachet\Models\Schedule
     *
     * @authenticated
     */
    public function update(UpdateScheduleRequestData $data, Schedule $schedule, UpdateSchedule $updateScheduleAction)
    {
        $this->guard('schedules.manage');

        $updateScheduleAction->handle($schedule, $data);

        return ScheduleResource::make($schedule->fresh());
    }

    /**
     * Delete Schedule
     *
     * @response 204
     *
     * @authenticated
     */
    public function destroy(Schedule $schedule, DeleteSchedule $deleteScheduleAction)
    {
        $this->guard('schedules.delete');

        $deleteScheduleAction->handle($schedule);

        return response()->noContent();
    }
}
