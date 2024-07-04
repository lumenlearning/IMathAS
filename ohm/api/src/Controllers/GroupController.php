<?php

namespace OHM\Api\Controllers;

use DI\Container;
use Monolog\Logger;
use OHM\Api\Services\GroupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
     * @return Response
	 */
    public function findAll(Request $request, Response $response, array $args): Response
	{
		list($pageNum, $pageSize) = $this->getPaginationArgs($request);

        $queryParams = $request->getQueryParams();
		$nameFilter = $queryParams['name_filter'] ?? '';

		$groups = Group::take($pageSize)->skip($pageSize * $pageNum);
		if (!empty($nameFilter)) $groups = $groups->where('name', 'like', "%{$nameFilter}%");
		$groups = $groups->get();

        $payload = json_encode($groups);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Get a single group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
     * @return Response
	 */
    public function find(Request $request, Response $response, array $args): Response
	{
		$group = $this->groupService->findByIdOrUuid($args['id']);

		if (empty($group)) {
			return $response->withStatus(404);
		}

        $payload = json_encode($group);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Create a group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function create(Request $request, Response $response, array $args): Response
	{
		$newGroupData = $request->getParsedBody();

		$existingGroup = Group::where('name', $newGroupData['name'])->first();
		if (!is_null($existingGroup)) {
            $payload = json_encode(['errors' => ['The specified group name already exists.']]);
            $response->getBody()->write($payload);
            return $response
                ->withStatus(409)
                ->withHeader('Content-Type', 'application/json');
		}

		$savedGroup = Group::create($newGroupData);

		$this->logger->info('Created new group.', [
			'groupId' => $savedGroup->id,
			'groupName' => $savedGroup->name,
			'lumenGuid' => $savedGroup->lumen_guid
		]);

        $payload = json_encode($savedGroup);
        $response->getBody()->write($payload);
        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Delete a group.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function delete(Request $request, Response $response, array $args): Response
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
    public function update(Request $request, Response $response, array $args): Response
	{
		$groupId = $args['id'];
        $groupAttributes = $request->getParsedBody();

        $group = $this->groupService->updateByIdOrUuid($groupId, $groupAttributes);

        if (is_null($group)) {
            return $response->withStatus(404);
        }

        $payload = json_encode($group);
        $response->getBody()->write($payload);
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
	}
}
