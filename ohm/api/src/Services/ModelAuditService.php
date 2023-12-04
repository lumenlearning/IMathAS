<?php

namespace OHM\Api\Services;

use Illuminate\Database\Eloquent\Model;
use Monolog\Logger;
use Slim\Container;

/**
 * This provides a simple method to log changes to Models.
 *
 * Note that this method must be manually called right before
 * saving a model.
 *
 * Long-term, something like \OwenIt\Auditing should be used. At the
 * time of creating this class, adding this to the project would
 * require an amount of effort that is out of scope for OHM-1193.
 */
class ModelAuditService
{
    private Logger $logger;

    public function __construct(Container $container)
    {
        $this->logger = $container->get('logger');
    }

    /**
     * Log changes to a model.
     *
     * This method should be called AFTER the model has been changed,
     * but BEFORE it's been saved.
     *
     * @param Model $model The modified model.
     * @param array $requestedChanges The new model attributes as an
     *                                associative array.
     * @return void
     */
    public function logChanges(Model $model, array $requestedChanges)
    {
        $modelName = get_class($model);
        $originalData = $model->getOriginal();
        $updatedData = $model->getAttributes();

        $logMessage = sprintf("Updating %s:
Original model attributes:
%s
Requested changes:
%s
New model attributes:
%s
",
            $modelName,
            print_r($originalData, true),
            print_r($requestedChanges, true),
            print_r($updatedData, true)
        );

        $this->logger->info($logMessage);
    }
}
