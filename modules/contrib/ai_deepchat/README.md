# AI DeepChat Module

## Overview

The **AI DeepChat** module is a repurposed version of the AI Chatbot module, now fully integrated with *
*[Deep Chat](https://www.deepchat.dev)**.
It provides the same functionality as the AI Chatbot module, serving as a boilerplate frontend for AI assistant
interactions,
with enhanced extensibility through Deep Chat's advanced features.

## Features

- **Text-based conversations**: Enables user-friendly text interactions with the AI assistant through a block interface.
- **Deep Chat integration**: Utilizes Deep Chat's scalable framework and modern features.
- **Future extensibility**: Designed for additional features, including:
  - Image uploads
  - Audio recording
  - Speech commands
  - And much more!

**Note**: These features are not yet implemented in the current version of the module, while Deep Chat offers them
    as part of its core functionality. Feel free to contribute and enhance.

## Getting Started

### Installation

1. Download and enable the module using Composer and Drush:
   ```bash
   composer require drupal/ai_deepchat
   drush en ai_deepchat
   ```
This should download the module and its dependencies, and enable it on your Drupal site.

### Usage
- Customize the block’s placement and appearance via the Block Layout interface.
- In the Block settings form, configure the AI assistant’s appearance and behavior.
- Add permission to the roles which are allowed to access the API at `/admin/people/permissions`.

### Limitations
There are currently no exposed settings to change the speed and chunk size of streaming - they are hardcoded
in the module. Furthermore, the remember setting is not used, the ChatBot remembers its state
via localstorage by default (open or close).

## Additional Information
This module wouldn't been possible without the fantastic work of the AI Chatbot module maintainers and contributors as well as the Deep Chat project under https://www.deepchat.dev.
For more information on how to set up, please see the [AI Assistant API module documentation](https://project.pages.drupalcode.org/ai/modules/ai_assistant_api/index.md).

