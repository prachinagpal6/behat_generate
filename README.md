This is the 8.x version Behat Generator.

Behat Generator - https://www.drupal.org/project/behat_generate

## Introduction

This project is a tool which provides drush commands to generate Behat tests automatically based on the configuration already created for the site.

This is a quick way to set up tests once functionality has already been implemented.


## Installation

1. Ensure you have Behat installed and running along with your Drupal project. [Behat Quick Start](http://behat.org/en/latest/quick_start.html)
2. Ensure you have drush installed globally or for the project. [Install Drush](https://docs.drush.org/en/master/install/)
2. See Drupal [module installation guidelines](http://drupal.org/getting-started/install-contrib/modules)
3. Goto `admin/config/development/behat-generate` and specify the installation path for Behat. By default it should be under `/test/behat`


## How it works
 1. Once you have Behat, Drush and Behat Generator setup.
 2. In you console you can type your behat generator commands:
    ```
    drush generate behat-generator-content-test
    ```
 3. You should notice new feature files create under `behat/features`
 4. To see all available behat generate command simply run `drush`



## Troubleshooting

1. If you have having issues with fields for which test is not being generated, first try the module with a clean Drupal install
with the fields available in core.

2. If you are using a 3rd party module that provides custom field plugins, try
disabling the field and run the command again. That will
help to narrow down any issues related to the fields.


## Help and Assistance

Help is available in the issue queue. If you are asking for help, please
provide the following information with your request:

- The behat Installation path
- Drush version
- PHP version
- Drupal core version
- Behat Version
- The behat generator command that has issue
- The field type for which it is failing (if applicable)
- Any additional details that the module authors can use to reproduce this
	with a default installation of Drupal.
	
---
Created by Prachi Nagpal.