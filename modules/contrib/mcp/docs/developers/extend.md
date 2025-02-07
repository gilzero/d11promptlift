# Extending the MCP Module with Custom Plugins
The MCP module supports a plugin-based architecture, allowing developers to extend its functionality in a modular and decoupled way. Plugins enable you to introduce additional processing capabilities, custom features, or integrations with external services.
This guide will explain the following:
- **How the MCP plugin system works**
- **Step-by-step implementation of a custom plugin**
- **Examples of use cases for creating new plugins**

## How the MCP Plugin System Works
The MCP module's plugin system is built on top of the Drupal plugin architecture. It provides a common **Plugin Manager** and a base plugin class to standardize the creation and use of plugins.

Developers can add custom plugins under `src/Plugin/Mcp/` for easy integration into the module system.

## Creating a Custom Plugin

Create a new PHP class for your plugin in the `src/Plugin/Mcp` directory.
- **Extend the Base Class**: Your new class must extend `McpPluginBase`.
- **Add Annotations**: Add the required @Annotation to define plugin metadata such as ID, label, and supported contexts.

The MCP Plugin Manager will automatically discover your plugin.

## Use Cases for MCP Plugins

By creating the plugin you can implement MCP server features like exposing a resource, which can be the Drupal content types or view outputs or literally anything that can be located with URI (check the MCP protocol concepts for more details). Or you can expose the tool, which on its own, can be any function.

You can check our sub-modules for the reference.
