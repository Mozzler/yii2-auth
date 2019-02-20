## Installation

Add the following to the application config:

```
// add oauth2 bootstrap
$config['bootstrap'] = ['log','oauth2'];

// add Oauth2 module to facilitate OAuth functionality
$config['modules']['oauth2'] = [
    'class' => '\mozzler\auth\OauthModule'
];

// add Mozzler auth module to enable the client application admin interface
$config['modules']['oauth2'] = [
    'class' => '\mozzler\auth\Module'
];
```

See `mozzler\auth\OauthModule` for default configuration which can override any of `filsh\yii2\oauth2server\Module` configuration options.

When making a request to fetch a `token` you must supply a valid `username` and `password`. The default User model uses the `email` field as the username. This can be customised by modifying the public `usernameField` attribute on your User model.

Note: Custom table names are not yet supported.

Add the following configuration to `config/params.php`:

```
'mozzler.auth' => [
      'user' => [
          'passwordReset' => [
              "invalidToken" => "Your link to reset password has expired",
              "emailMismatch" => "Email address does not match token",
              "successMessage" => "Password successfully reset, please login",
              'redirectUrl' => '/user/login',
              'tokenExpiry' => 60*60,        // 1 hour
          ]
      ]
  ]
```

## Create client application

In order to use OAuth it must be configured with a client application. This is a `client-id` and `client-secret` that is sent to the server by the application using the API. This enables the server to identify the connecting application and remove it's access if required.

There is a default admin interface for creating new client applications which can be found at `/auth/oauthclient`. A new client application can be created at `/auth/oauthclient`.

## Create a new user

A user must exist before you can request an `access_token`. The user can either be created via the admin interface for your application, or via the API. For the latter, you must ensure the `/user/create` endpoint is public and the `create` scenario on your application User model includes the `password` field so the API can specify the user's password.

## Generate a token using credentials

In order to use the API, you must have an `access_token`. Obtain an access token by calling the `/user/token` endpoint and supplying valid JSON in the body:

```
{
  "username": "email@gmail.com",
  "password": "<password>",
  "client_id": "application-id",
  "client_secret": "application-secret",
  "response_type": "token",
  "grant_type": "password"
}
```

Note: Replace `/user/` with the appropriate `user` endpoint if using a custom User model with a different controller name or routing rules.

This will produce a response that provides a valid `access_token` and `refresh_token`:

```
{
    "access_token": "dcc1cd7320d9bc163c01c2c47a9a9522c7510d02",
    "expires_in": 86400,
    "token_type": "Bearer",
    "scope": null,
    "refresh_token": "aff45f84ba11f5e1b7dc1e0206f725222bf85903",
    "user_id": "5c2051de0b6ee808a8523da8"
}
```

The `access_token` will be sent with all future API requests to verify the user.

The `refresh_token` must be saved and can be used to obtain a fresh `access_token` when it expires.

Curl example:

```
curl -v -i -H "Content-Type: application/json" -H "Accept:application/json" -H "Host: test-api.localhost" -X POST -d '{"username": "email@gamil.com", "password":"<password>", "client_id": "application-id", "client_secret": "application-secret", "response_type": "token", "grant_type": "password"}' http://localhost/web/user/token | more
```

## Generate a token using refresh token

Obtain a new `access_token` by calling the `/user/token` endpoiunt, but specifying the `grant_type` as `refresh_token` in the JSON body:

```
{
  "refresh_token": "aff45f84ba11f5e1b7dc1e0206f725222bf85903",
  "client_id": "application-id",
  "client_secret": "application-secret",
  "response_type": "token",
  "grant_type": "refresh_token"
}
```

Curl example:

```
curl -v -i -H "Content-Type: application/json" -H "Accept:application/json" -H "Host: test-api.localhost" -X POST -d '{"refresh_token": "aff45f84ba11f5e1b7dc1e0206f725222bf85903", "client_id": "appklication-id", "client_secret": "application-secret", "grant_type": "refresh_token"}' http://localhost/web/user/token | more
```

## Making API request

Use the `access_token` in the HTTP request header to make a valid request to the API:

```
Header:
Authorization: Bearer <access_token>
```

Curl example:

```
curl -v -i -H "Content-Type: application/json" -H "Accept:application/json" -H "Host: test-api.localhost" -H "Authorization: Bearer dcc1cd7320d9bc163c01c2c47a9a9522c7510d02" http://localhost/web/user | more
```

## Accessing user in Yii

Once a user authenticates by supplying a valid `Authorization: Bearer xxxxxxx` header, your application can access the requesting user via the normal Yii2 technique:

```
if (!\Yii::$app->user->isGuest) {
	$user = \Yii::$app->user->identity;
}
```