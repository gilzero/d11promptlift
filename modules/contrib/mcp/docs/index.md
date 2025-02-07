# What is MCP?

The **[Model Context Protocol (MCP)](https://modelcontextprotocol.io/)** is an open-source protocol recently introduced by [Anthropic](https://www.anthropic.com), the company behind [Claude](https://claude.ai). MCP is designed to establish a standard that enables large language model (LLM) applications to extend their context dynamically using both local and remote data sources.

This protocol is especially valuable when building custom AI workflows that require awareness of specific dynamic contexts or when enhancing the functionality of LLM-based tools like chat applications (e.g., Claude or ChatGPT) or development environments like Zed IDE.

MCP is built around three core components: **hosts**, **clients**, and **servers**. Host is the LLM application itself (e.g., Claude). Client can be thought as software embedded within the host to facilitate communication with the server and the server - the component that provides additional context and tools to the LLM by adhering to the MCP standard.

The protocol defines four foundational elements: **Resources**, **Prompts**, **Tools**, and **Sampling**.

**Resources** expose data to the LLM, making it accessible as part of its extended context. According to the MCP documentation, *“Resources are a core primitive…that allow servers to expose data and content that can be read by clients and used as context for LLM interactions.”

Examples of resources include files, database records, API responses and [others](https://modelcontextprotocol.io/docs/concepts/resources#overview).

**Prompts** allow servers to define templates with dynamic arguments, chaining, and other advanced features, enabling standardized and reusable interactions with LLMs.

**Tools** represent functions on the server that can be invoked by the LLM to perform actions, such as database queries or API calls, enhancing the LLM’s ability to interact with external systems.

**Sampling** involves requesting autocompletions or predictions from LLMs. Although not yet implemented (even in Claude), it offers a promising avenue for future capabilities.

Even with just this overview, the potential for building powerful solutions using this approach to extend LLM context is clear. For more in-depth details, refer to the official [MCP documentation](https://modelcontextprotocol.io/).

# Drupal MCP Server

Despite its recent introduction, MCP holds tremendous potential. While only Claude and Zed IDE currently support it, the protocol’s open nature suggests broader adoption by major AI providers and LLM-based applications. Even in its current state, MCP is particularly useful for building custom AI workflows within organizations, enabling AI to operate contextually with both data and actions.  

Here are some, very limited examples of how MCP can be applied:

**Connecting to a Local Database**: As described in the documentation, you can directly link Claude to an SQLite database and use its entire dataset as an extended context for queries and workflows.

**Enhanced Customer Support**: AI chatbots could access real-time customer records to provide highly contextual responses.

**Code Review Automation**: IDE tools could dynamically pull contextual project details from external repositories and databases.

**Editorial Assistance**: MCP-enabled workflows could allow editorial teams to query content databases or invoke AI-powered editing tools.

## Drupal as an MCP Server

Drupal obviously is uniquely positioned to serve as an MCP server due to its robust content management capabilities and modular structure. Drupal’s content can be exposed as **resources**, its built-in [AI functionality](htts://drupal.org/project/ai) can be leveraged as **tools**, and its extensibility enables creating highly customized workflows.

By acting as an MCP server, Drupal becomes a powerful platform for integrating AI-driven functionality, allowing organizations to build smarter and more efficient content workflows.

This module enables Drupal to comply with the MCP standard, transforming it into an MCP server upon installation and configuration. Serving as a foundational layer, it not only provides MCP server APIs compatible with LLM applications but also allows developers and other modules to extend its capabilities by defining and exposing resources, tools, and prompts as MCP elements