# MCP

The MCP Drupal module implements the Model Context Protocol (MCP), enabling your Drupal site to act as an MCP server.

This module is planned to integrate deeply with the existing AI ecosystem, enabling AI agents to implement MCP plugins
and seamlessly interact with the MCP server, fostering a unified, flexible and extensible context-aware AI experience
from your Drupal website.

## Dependencies
- PHP ^8.3
- Drupal ^10 || ^11
- For development:
  - Bun ^0.1.0

## Installation and Configuration
1. Install the module using Composer:
   ```bash
   composer require drupal/mcp
   ```
2. Enable the module:
   ```bash
    drush en mcp
    ```
3. Configure the `mcp-server-drupal`
   - Download the [MCP Server Drupal](mcp-server-drupal/build/mcp-server-drupal) binary.
   - Place the binary in somewhere in your local machine and make it executable:
     ```bash
     chmod +x mcp-server-drupal
     ```
   - Configure the mcp client, for example modify the Claude desktop client configuration file and add the Drupal server:
      ```json
      {
        "mcpServers": {
          "mcp-server-drupal": {
            "command": "__BINARY_PATH__",
            "args": ["--drupalBaseUrl", "__DRUPAL_BASE_URL__"],
            "env": {}
          }
        }
      }
      ```

## Important Notes
- ⚠️ Currently the module not yet have authentication and authorization mechanisms. ⚠️
- This module is under active development and is not yet ready for production use.
- Use at your own risk.
- Everything is subject to change.
