# Lumen Question and Scoring Api

## Composer Install

The project currently runs from a sub-folder of the existing ohm application and does not use ohm's existing composer.json or vendor directory. It has its own composer.json file and must therefore have composer run separately from ohm composer from within project root.

From within ./ohm/lumenapi
```bash
 composer install
```

## Configuration

Project configuration is currently handled via .env file located within project root. 

A .env.sample is provided and should be renamed to .env and updated with the appropriate values.

## Swagger

The Swagger UI page is currently accessed via: http://localhost/ohm/lumenapi/public/api/documentation.

Data for the UI page is located in ./storage/api-docs/app-docs.json.

This file is generated based on annotations in the ./app/Http/Controllers files and can be regenerated using the following command run from project root.

```bash
php artisan swagger-lume:generate
```

## API Token

A JWT bearer token must be provided on the Authroization header for all secured API calls. A POST endpoint is provided at /token for generating a new token for subsequent requests. The parameters required for this endpoint are client_id and client_secret. Both values are set in the .env file.

Request
```json
POST /token

{
  "client_id": "secure-client-id",
  "client_secret": "secure-secret" 
}
```

Response
```json
{
    "access_token": "three.segment.JWT",
    "token_type": "Bearer"
}
```

The access_token value returned in the respose is then used for all other API requests.

Header example
```json
Authorization: Bearer <three.segment.JWT>
```
