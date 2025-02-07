# Install and Setup

Following are the steps how to install and setup MCP module with your local claude desktop app.

```bash
# require the module
composer require 'drupal/mcp:^1.0@alpha'

# enable the module (this can also be done with UI)
drush en mcp
```

There are two ways to use your Drupal with the LLM clients using MCP protocol.

## Drupal Native

First one is the simplest way, Drupal to become the MCP server itself (using the HTTP SSE). This works out of the box after you enable this module.

## Third Party MCP Server

Second way is to use the third-party MCP server acting as an intermediary between your Drupal instance and the LLM client.

Here you can download ts-based [Drupal MCP server](https://github.com/Omedia/mcp-server-drupal/releases/tag/v1.0.0-alpha2) binary, meant to work with this module and connect to Drupal.

This one is meant to work with local LLM software requiring the stdio for the communication.

Put the downloaded binary somewhere on your system and make sure, its executable.

After this you need to define MCP server for your LLM software. For example, for claude, in the configuration file. The configuration looks like the following:

```json
{
  "mcpServers": {
    "mcp-server-drupal": {
      "command": "full path to the executable",
      "args": ["--drupalBaseUrl", "your drupal website url"],
      "env": {}
    }
  }
}
```

Finally, after this configuration is set and you restart your claude dekstop app, you will be able to access and use mcp server.



