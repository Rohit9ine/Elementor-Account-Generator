# Elementor Account Generator

## Description
The **Elementor Account Generator** plugin creates an account through a webhook when a button is clicked in an Elementor widget.

## Features
- Provides an Elementor widget to generate accounts via a webhook.
- Displays the account credentials upon successful creation.
- Includes AJAX handling for account generation.
- Allows copying of generated account details for easy access.

## Installation
1. Download the plugin files.
2. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Ensure you have Elementor installed and activated.

## Usage
1. Drag and drop the "Account Generator" widget into your Elementor page.
2. Click the "Generate Your Account" button to create an account via the webhook.
3. The account credentials will be displayed once the account is created successfully.

## Shortcode
You can also use the shortcode `[account_generator]` to place the account generator button or credentials display anywhere in your posts or pages.

## Example Shortcode
Place the following shortcode in any post or page where you want the account generator to appear:
```sh
[account_generator]
