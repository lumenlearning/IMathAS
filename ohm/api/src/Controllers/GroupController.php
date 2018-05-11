<?php

namespace OHM\Api\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

use OHM\Models\Group;

class GroupController extends BaseApiController
{
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

		$groups = Group::take($pageSize)->skip($pageSize * $pageNum);
//		 conditional sql goes here
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
		$groupId = $args['id'];

		$group = Group::find($groupId);
//		 conditional sql goes here

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

		$group = Group::find($groupId);
		if (is_null($group)) {
			return $response->withStatus(204);
		}

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

		$group = Group::find($groupId);
		if (is_null($group)) {
			return $response->withStatus(404);
		}

		$group->fill($request->getParsedBody());
		$group->save();

		return $response->withStatus(200)->withJson($group);
	}
}
