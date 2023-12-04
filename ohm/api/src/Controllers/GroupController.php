<?php

namespace OHM\Api\Controllers;

use Monolog\Logger;
use OHM\Api\Services\GroupService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

use OHM\Models\Group;

class GroupController extends BaseApiController
{
	private Logger $logger;

    private GroupService $groupService;

	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->logger = $container->get('logger');
        $this->groupService = $container->get('groupService');
	}

	/**
	 * Get all groups.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return null
	 */
	public function findAll($request, $response, $args)
	{
		list($pageNum, $pageSize) = $this->getPaginationArgs($request);

		$nameFilter = $request->getParam('name_filter');

		$groups = Group::take($pageSize)->skip($pageSize * $pageNum);
		if (!empty($nameFilter)) $groups = $groups->where('name', 'like', "%{$nameFilter}%");
		$groups = $groups->get();

		return $response->withJson($groups);
	}

	/**
	 * Get a single group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return null
	 */
	public function find($request, $response, $args)
	{
		$group = $this->groupService->findByIdOrUuid($args['id']);

		if (empty($group)) {
			return $response->withStatus(404);
		}

		return $response->withJson($group);
	}

	/**
	 * Create a group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function create($request, $response, $args)
	{
		$newGroupData = $request->getParsedBody();

		$existingGroup = Group::where('name', $newGroupData['name'])->first();
		if (!is_null($existingGroup)) {
			return $response->withStatus(409)
				->withJson(['errors' => ['The specified group name already exists.']]);
		}

		$savedGroup = Group::create($newGroupData);

		$this->logger->info('Created new group.', [
			'groupId' => $savedGroup->id,
			'groupName' => $savedGroup->name,
			'lumenGuid' => $savedGroup->lumen_guid
		]);

		return $response->withStatus(201)->withJson($savedGroup);
	}

	/**
	 * Delete a group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function delete($request, $response, $args)
	{
		$groupId = $args['id'];

		$group = $this->groupService->findByIdOrUuid($groupId);
		if (is_null($group)) {
			return $response->withStatus(204);
		}

		$this->logger->info('Deleting a group.', [
			'groupId' => $group->id,
			'groupName' => $group->name,
			'lumenGuid' => $group->lumen_guid
		]);

		$group->delete();

		return $response->withStatus(204);
	}

	/**
	 * Update a group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function update($request, $response, $args)
	{
		$groupId = $args['id'];
        $groupAttributes = $request->getParsedBody();

        $group = $this->groupService->updateByIdOrUuid($groupId, $groupAttributes);

        if (is_null($group)) {
            return $response->withStatus(404);
        }

        return $response->withStatus(200)->withJson($group);
	}
}
