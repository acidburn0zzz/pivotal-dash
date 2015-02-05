# pivotal-dash
A dashboard for getting a better overview of your team and projects from Pivotal Tracker


## Synopsis

This is a simple symfony2 application, that uses guzzle for interacting with the Pivotal Tracker API. 

## Motivation

If you use Pivotal Tracker with multiple projects, it can be hard to get a good overview across projects and teams, so this dashboard helps with that. It was developed for use at packet.net for doing our team standups.

## Installation

Once you download the code, you'll need to install composer, and do a composer update to install the required packages. You will also need to add the following parameters to your parameters.yml file:


    pivotal.endpoint: https://www.pivotaltracker.com/services/v5
    pivotal.token: YOURPIVOTALAPITOKEN
    pivotal.account: YOURPIVOTALACCOUNTID

## Contributors

Feel free to make improvements and submit pull requests if you do something neat with it!

## License

Released under the Apache License, Version 2.0 as described in the LICENSE file

