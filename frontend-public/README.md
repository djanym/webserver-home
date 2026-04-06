# Frontend Public Configuration

## Setup

This directory contains the compiled frontend assets and configuration for the webserver-home application.

### Configuration File

The application requires an `app-public-config.json` file to run. This file contains the API endpoint URLs.

#### Steps to setup:

1. **Copy the sample configuration**:
   ```bash
   cp sample.app-public-config.json app-public-config.json
   ```

2. **Fill in the configuration values** in `app-public-config.json`:

   | Property | Description | Example                            |
      |----------|-------------|------------------------------------|
   | `appBaseUrl` | The base URL where the application is served | `https://localhost/webserver-home` |
   | `apiBaseUrl` | The base URL for the API backend | `https://localhost/webserver-home/backend` |

3. **Example filled configuration**:
   ```json
   {
       "appBaseUrl": "https://home.server.local",
       "apiBaseUrl": "https://api.homeweb.server.local"
   }
   ```

### Note

- The `app-public-config.json` file is not tracked by git for portability across different environments (development, staging, production).

