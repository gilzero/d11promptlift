# Metatag AI

Welcome to Metatag AI, an open-source Drupal project developed by Promet Source.
This module leverages Open AI to automatically generate meta descriptions for 
various content types. The meta tags are dynamically created based on the title 
and description of each piece of content, enhancing the SEO capabilities of your 
Drupal website.

# POST-INSTALLATION

1. Once metatag is enabled, add field_metatag in your chosen content type.
2. Configure the metatag_ai by navigating to /admin/config/content/metatag-ai.
Choose between OpenAI and AWS Bedrock and configure it accordingly.
3. Visit /admin/config/content/metatag-ai/content-type to select the content 
types where you added the field in Step #1.
4. Add the machine name of the Metatag field.
4. Create content of the selected types by entering a title and body.
5. Click on "Generate Metatag" button to automatically generate meta tags 
based on the content.
6. Verify if the Metatag basic title, description, abstract and keywords are 
updated.

## OpenAI API Keys

To retrieve your OpenAI keys, follow these steps:

1. Login to https://platform.openai.com
2. Go to API keys section. Follow the instructions provided to create your API keys.
3. Copy the API Key and a Secret Key.

## AWS API Keys

To retrieve your AWS keys, follow these steps:

1. Go to AWS Security Credentials.
2. Click on "View Access keys."
3. If you haven't created an access key yet, create one.
4. Copy both the Access Key and Secret Access Key.

# CREDIT

Metatag AI is a fork of the Open AI Metadata project. The configuration is the 
same, with the key distinction being its dependency on the Metatag module.

Metatag AI is a project developed and maintained by Promet Source. For more 
information about Promet Source and their contributions to the Drupal community, 
visit Promet Source.
