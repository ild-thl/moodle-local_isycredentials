# moodle-local_isycredentials

Moodle Plugin for creating and issuing digital credentials.

## Installation

To install this plugin, follow these steps:

1. Navigate to the `local` plugin folder of your Moodle system.
2. Clone the repository using the following command:

    ```sh
    git clone https://github.com/ild-thl/moodle-local_isycredentials.git isycredentials
    ```

3. Complete the installation through the Moodle plugin management interface.

## Requirements

This plugin requires either an EDCI Issuer or a DigitalSignatureService (DSS) to function. These services can be run using Docker and the following `docker-compose` configuration:

### DigitalSignatureService

```yaml
dss:
    build: ../dss
    restart: unless-stopped
    ports:
        - "8089:8080"
```

### EDCI-Issuer

```yaml
issuer:
    image: code.europa.eu:4567/qualifications-courses-and-credentials/european-digital-credentials/issuer
    container_name: issuer
    userns_mode: keep-id
    user: edci
    environment:
        - JPDA_ENABLED=true
        - JPDA_TRANSPORT=dt_socket
        - JPDA_ADDRESS=*:8000
        - WAIT_HOSTS=mysqldb:3306
        - WAIT_TIMEOUT=60
    volumes:
        - "../edci/docker_issuer/edci:/usr/local/tomcat/conf/edci"
        - "../edci/docker_issuer/credentials:/usr/local/tomcat/temp/credentials"
        - "../edci/docker_issuer/logs:/usr/local/tomcat/logs"
    ports:
        - "8383:8080"
    depends_on:
        mysqldb:
            condition: service_healthy


mysqldb:
    image: code.europa.eu:4567/qualifications-courses-and-credentials/european-digital-credentials/mysqldb
    userns_mode: keep-id
    container_name: mysqldb
    environment:
        - MYSQL_ROOT_PASSWORD=changeMe
        - MYSQL_USER=edci
        - MYSQL_PASSWORD=changeMe
    volumes:
        - "../edci/docker_mysql/datadir:/var/lib/mysql"
    ports:
        - 3307:3306
    healthcheck:
        test: ["CMD-SHELL", "mysqladmin ping"]
        timeout: 5s
        retries: 6
```
