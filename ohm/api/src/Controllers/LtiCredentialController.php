<?php

namespace OHM\Api\Controllers;

use DI\Container;
use OHM\Models\Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use OHM\Models\LtiCredential;

/**
 * Class LtiCredentialController
 * @package OHM\Api\Controllers
 * @see LtiCredential for JSON payload keys to DB column name mappings.
 */
class LtiCredentialController extends BaseApiController
{
	private $logger;

	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->logger = $container->get('logger');
	}

	/**
	 * Get all LTI credentials.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
     * @return Response
	 */
    public function findAll(Request $request, Response $response, array $args): Response
	{
		list($pageNum, $pageSize) = $this->getPaginationArgs($request);
		$groupId = $this->getGroupId($args['groupId']);

        $queryParams = $request->getQueryParams();
		$domainFilter = $queryParams['domain_filter'] ?? '';

		$creds = LtiCredential::take($pageSize)->where('groupid', $groupId)
			->skip($pageSize * $pageNum);
		if (!empty($domainFilter)) $creds = $creds->where('email', 'like', "%{$domainFilter}%");
		$creds = $creds->get();

		$publicCreds = array_map([$this, 'mapOhmSchema2Public'], $creds->all());

        $payload = json_encode($publicCreds);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Get a single LTI credential.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
     * @return Response
	 */
    public function find(Request $request, Response $response, array $args): Response
	{
		$cred = LtiCredential::find($args['id']);

		if (empty($cred)) {
			return $response->withStatus(404);
		}

		$publicCred = $this->mapOhmSchema2Public($cred);

        $payload = json_encode($publicCred);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Create an LTI credential.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function create(Request $request, Response $response, array $args): Response
	{
		$groupId = $this->getGroupId($args['groupId']);
		$rawCredData = $request->getParsedBody();

		$newCredData = $this->mapPublic2ohmSchema($rawCredData, true);
		$newCredData['groupid'] = $groupId;

		if ($this->containsNulls($newCredData)) {
            $payload = json_encode(['errors' => ['Null values are not permitted.']]);
            $response->getBody()->write($payload);
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
		}

		$existingCred = LtiCredential::where('SID', $newCredData['SID'])->first();
		if (!is_null($existingCred)) {
            $payload = json_encode(['errors' => ['The specified LTI key already exists.']]);
            $response->getBody()->write($payload);
            return $response
                ->withStatus(409)
                ->withHeader('Content-Type', 'application/json');
		}

		$logCredData = $newCredData;
		unset($logCredData['password']);
		$this->logger->info('Creating LTI credential for group ID: ' . $groupId,
			$logCredData);

		$savedCred = LtiCredential::create($newCredData);

		$publicCred = $this->mapOhmSchema2Public($savedCred);

        $payload = json_encode($publicCred);
        $response->getBody()->write($payload);
        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Delete an LTI credential.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function delete(Request $request, Response $response, array $args): Response
	{
		$cred = LtiCredential::find($args['id']);
		if (is_null($cred)) {
			return $response->withStatus(204);
		}

		$this->logger->info('Deleting LTI credential.', [
			'key' => $cred->SID,
			'domain' => $cred->email,
			'group_id' => $cred->groupid
		]);

		$cred->delete();

		return $response->withStatus(204);
	}

	/**
	 * Update an LTI credential.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
    public function update(Request $request, Response $response, array $args): Response
	{
		$cred = LtiCredential::find($args['id']);
		if (is_null($cred)) {
			return $response->withStatus(404);
		}

		$rawCredData = $request->getParsedBody();
		$updatedData = $this->mapPublic2ohmSchema($rawCredData, false);

		if ($this->containsNulls($updatedData)) {
            $payload = json_encode(['errors' => ['Null values are not permitted.']]);
            $response->getBody()->write($payload);
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
		}

		$cred->fill($updatedData);
		$cred->save();

		$publicCred = $this->mapOhmSchema2Public($cred);

        $payload = json_encode($publicCred);
        $response->getBody()->write($payload);
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Return a public representation of LTI credential data.
	 *
	 * This converts keys in a hash from what exists in the OHM DB to
	 * what is expected by someone consuming the OHM API.
	 *
	 * There is a better way to do this, but we can't use it due to our need
	 * for data manipulation for columns like "rights".
	 * Reference for a better way: https://stackoverflow.com/a/43526516
	 *
	 * @param LtiCredential $ltiCredential An instance of LtiCredential.
	 * @return array A public-suitable associative array of the LtiCredential.
	 */
	private function mapOhmSchema2Public($ltiCredential)
	{
		$result = [];
		$result['id'] = $ltiCredential->id;
		$result['domain'] = $ltiCredential->email;
		$result['key'] = $ltiCredential->SID;
		$result['can_create_instructors'] = 76 == $ltiCredential->rights ? true : false;
		$result['group_id'] = $ltiCredential->groupid;
		$result['created_at'] = $ltiCredential->created_at->timestamp;
		return $result;
	}

	/**
	 * Return a representation of LTI credential data suitable for
	 * insertion into the OHM DB.
	 *
	 * A note about $isForNew:
	 *   Set this to true if you are about to create a new LtiCredential.
	 *   Set to false if you are updating an existing LtiCredential.
	 *
	 * Input data (a hash's keys, specifically) must be what a consumer of
	 * the OHM API sends us.
	 *
	 * There is a better way to do this, but we can't use it due to our need
	 * for data manipulation for columns like "rights".
	 * Reference for a better way: https://stackoverflow.com/a/43526516
	 *
	 * @param array $data An associative array as received by an OHM API consumer.
	 * @param bool $isForNew Are you creating a new LtiCredential?
	 * @return array An associative array suitable for insertion into the OHM DB.
	 */
	private function mapPublic2ohmSchema($data, $isForNew)
	{
		$result = [];
		$result['email'] = isset($data['domain']) ? $data['domain'] : null;
		$result['SID'] = isset($data['key']) ? $data['key'] : null;
		$result['groupid'] = isset($data['group_id']) ? $data['group_id'] : null;
		$result['FirstName'] = $result['email'];
		$result['LastName'] = 'LTIcredential';

		if (isset($data['can_create_instructors'])) {
			$result['rights'] = $data['can_create_instructors'] ? 76 : 11;
		} else {
			$result['rights'] = null;
		}

		/*
		 * When updating an existing LtiCredential, a missing password
		 * value AND key is okay.
		 * $this->containsNulls() is used to check for nulls.
		 *
		 * Only set nulls (for $this->containsNulls to find) for new
		 * LtiCredential records, and when an actual null pw is passed in.
		 */
		if ($isForNew || array_key_exists('secret', $data)) {
			$result['password'] = isset($data['secret']) ? $data['secret'] : null;
		}

		return $result;
	}

	/**
	 * Determine if an associative array contains null values.
	 *
	 * @param array $data An associative array.
	 * @return bool True if null values found, false if not.
	 */
	private function containsNulls($data)
	{
		foreach ($data as $k => $v) {
			if (is_null($v)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get a Group by its ID(int) or Lumen GUID.
	 *
	 * If the specified ID is an integer, it will be returned as-is.
	 * If the specified ID is a Lumen GUID, the group's ID is returned.
	 *
	 * @param int|string $id The group ID or Lumen GUID.
	 * @return int|null The Group for the specified Lumen GUID.
	 */
	private function getGroupId($id)
	{
		if ((string)(int)$id == $id) return $id;

		$group = Group::where('lumen_guid', $id)->first();

		return $group ? $group->id : null;
	}
}
