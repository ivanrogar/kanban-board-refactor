### Prerequisites

- PHP 8.0.x
- [Composer](https://getcomposer.org/download/)
- [Symfony Server](https://symfony.com/download)

### Github
Create Github OAuth App with the following:

- Homepage URL: http://127.0.0.1:8000
- Authorization callback URL: http://127.0.0.1:8000/login/oauth/redirect

If you're not using Symfony server, use whatever domain you want for these URLs.

### Settings
Edit the .env file in the project root and change the Github parameters accordingly:
```console
GH_ACCOUNT=some_github_username
GH_CLIENT_ID=client_id
GH_CLIENT_SECRET=secret
GH_REPOSITORIES=comma_separated_repositories
GH_STATE=state_string_used_for_github_auth
GH_PAUSED_LABELS=comma_separated_pause_labels
```

#### Install dependencies and start server
```console
make dev
```

#### Run tests
```console
make test
```
