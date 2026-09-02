# moodle-local_isycredentials

Moodle Plugin for creating and issuing digital credentials.

## Installation

To install this plugin, follow these steps:

1. Navigate to the `local` plugin folder of your Moodle system.
2. Clone the repository using the following command:

    ```sh
    git clone https://github.com/ild-thl/moodle-local_isycredentials.git isycredentials
    ```

3. Install the plugin's Composer dependencies:

    ```sh
    cd isycredentials
    composer install --no-dev
    ```

4. Complete the installation through the Moodle plugin management interface.

## Requirements

This plugin uses CSC qualified signing with DSS orchestration. These services
can be run using Docker and the following `docker-compose` configuration:

### DigitalSignatureService

```yaml
# Digital Signature Service
dss:
    build:
        context: ../dss
        dockerfile: Dockerfile
        args:
            DSS_VERSION: "6.5"
            JAVA_VERSION: "26"
    restart: unless-stopped
    expose:
        - "8080"
```

## Running Tests

Run these commands from the Moodle root directory. This works whether Moodle
runs directly on the host, in Docker, in Podman, or in another environment.
The PHP CLI, Composer dependencies, and the Moodle test database must be
available in that environment.

```sh
cd /path/to/moodle
php admin/tool/phpunit/cli/init.php
```

Initialise the PHPUnit environment first, or repeat the initialisation when
Moodle reports that the environment was created for a different Moodle
version. Then run the credential plugin test cases:

```sh
php vendor/bin/phpunit local/isycredentials/tests/credential_test.php
```

Run one test file individually when working on a specific area:

```sh
php vendor/bin/phpunit local/isycredentials/tests/credential_test.php
```
