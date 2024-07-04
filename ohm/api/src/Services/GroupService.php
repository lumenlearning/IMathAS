<?php

namespace OHM\Api\Services;

use DI\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Monolog\Logger;
use OHM\Models\Group;

class GroupService
{
    private Logger $logger;
    private ModelAuditService $modelAuditService;

    public function __construct(Container $container)
    {
        $this->logger = $container->get('logger');
        $this->modelAuditService = $container->get('modelAuditService');
    }

    /**
     * Get a Group by ID or UUID.
     *
     * @param int|string $id A Group ID or Lumen GUID.
     * @return Group|null
     */
    public function findByIdOrUuid($id): ?Group
    {
        if (is_numeric($id) && (int)$id == $id) {
            $group = Group::find($id);
        } else {
            $group = Group::where('lumen_guid', $id)->first();
        }

        return $group;
    }

    /**
     * Update Group attributes.
     *
     * Group payment settings will be applied to all of the group's
     * courses.
     *
     * @param int|string $groupId The Group's ID or Lumen GUID.
     * @param array $groupAttributes Group attributes as an associative array.
     * @return Group|null The update Group. Null if the Group doesn't exist.
     * @throws \Throwable Thrown on failure to update groups and courses.
     */
    public function updateByIdOrUuid($groupId, array $groupAttributes): ?Group
    {
        $group = $this->findByIdOrUuid($groupId);
        if (is_null($group)) {
            return null;
        }

        DB::beginTransaction();

        try {
            // The contents of $groupAttributes is currently provided by
            // the payment service. See: GroupController::update()
            $group->fill($groupAttributes);

            // If the payment service doesn't provide this value, then the
            // group's payment settings won't be modified. In this case, we
            // shouldn't touch course payment settings.
            if (isset($groupAttributes['student_pay_enabled'])) {
                $this->updatePaymentSettingAllCourses($group, $groupAttributes['student_pay_enabled']);
            }

            $this->modelAuditService->logChanges($group, $groupAttributes);
            $group->save();
            DB::commit();
        } catch (\Throwable $e) {
            $logMessage = sprintf("Failed to update payment setting for OHM group: %s (ID: %d)\nError: %s\nTrace: %s",
                $group->name, $group->id, $e->getMessage(), $e->getTraceAsString());
            $this->logger->error($logMessage);
            DB::rollBack();
            throw $e; // Allow SlimPHP to return an error response.
        }

        return $group;
    }

    /**
     * Update the payment setting for all courses owned by a teacher
     * user's group.
     *
     * @param Group $group The teacher user's Group.
     * @param int $isEnabled The new student payment setting. (0 = false, 1 = true)
     * @return int The number of courses updated.
     */
    private function updatePaymentSettingAllCourses(Group $group, int $isEnabled): int
    {
        $courses = $group->courses;

        $logMessage = sprintf(
            'Syncing payment setting on %s course(s) for group "%s" (group ID %d) to group payment setting. (setting == %d)',
            $courses->count(), $group->name, $group->id, $isEnabled);
        $this->logger->info($logMessage);

        $courses->each(function ($course) use ($isEnabled) {
            if ($course->student_pay_required == $isEnabled) {
                return;
            }
            $logMessage = sprintf('Updating course ID: %d, name: %s (set payments to %d)',
                $course->id, $course->name, $isEnabled);
            $this->logger->info($logMessage);
            $course->student_pay_required = $isEnabled;
            $course->save();
        });

        return 0;
    }
}
