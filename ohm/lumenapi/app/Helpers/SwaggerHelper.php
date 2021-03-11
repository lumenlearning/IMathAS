<?php

if (! function_exists('swagger_lume_asset')) {
    /**
     * Returns asset from swagger-ui composer package.
     *
     * @param $asset string
     *
     * @return string
     * @throws \SwaggerLume\Exceptions\SwaggerLumeException
     */
    function swagger_lume_asset($asset)
    {
        $file = swagger_ui_dist_path($asset);

        if (! file_exists($file)) {
            throw new SwaggerLumeException(sprintf('Requested L5 Swagger asset file (%s) does not exists', $asset));
        }

        $secure = env('APP_LOAD_BALANCED', true);
        if (empty(env('APP_LOAD_BALANCED')) && app()->environment('local','development')) {
            $secure = false;
        }

        return route('swagger-lume.asset', ['asset' => $asset, 'v' => md5($file)], $secure);
    }
}
