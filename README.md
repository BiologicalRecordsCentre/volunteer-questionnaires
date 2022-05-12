# Volunteer Questionnaires

A Drupal module that facilitates requesting the completion of questionnaires by volunteer teams.
Volunteers can opt-in to participation. The module tracks the questionnaires that a user has been
asked to complete and removes completed forms from the list. One questionnaire can be set as a
default questionnaire that all opted-in users are asked to complete.

This module provides a block listing the questionnaires for a user to complete.

Functionality for building questionnaires and managing submissions is provided by the
[Webform module](https://www.drupal.org/project/webform).

## Installation

The Volunteer Questionnaires module requires the [Webform module](https://www.drupal.org/project/webform).

Install the Volunteer Questionnaires module as normal.

Once installed, there is a new field in the user profile "Volunteer questionnaire opt-in". Modify
the user registration/profile form so this field is included at /admin/config/people/accounts/form-display,
allowing users to opt-in.

There is also a new field "Requested questionnaires" which shows the forms that a user has been
requested to complete which they haven't yet completed. Optionally add this to the user's profile
View page at /admin/config/people/accounts/display, selecting the "Link to form" format.

The module also adds a block "Requested volunteer questionnaires" which can be added to your site
using the blocks system to show a user any form they have been requested to complete which they
haven't yet completed. This is similar to the field described above but can be added anywhere in
the site, not just to the View user profile page. This can be themed using the template
`block--volunteer-questionnaires-requested-forms.html.twig`.

Webforms can now be created with your volunteer questionnaires. Refer to the documentation for the
[Webform module](https://www.drupal.org/project/webform).

When creating the form, if you want the IP address of the user submitting the form to be not
tracked, then there is an option Settings > Submission, "Disable the tracking of user IP address"
which should be ticked. You also need to configure the form to run the Volunteer Questionnaires
webform handler when forms are submitted - to do this, on the Settings > Email / Handlers tab,
click Add handler, Volunteer Questionnaires, then save the handler. This handler removes the user
ID from the form submission and also removes the form from the user's list of requested forms to
complete.

There is also an option in the module to select a default form which will be added to the list of
requested forms for all users who opt-in. The option to choose this form is at
/admin/config/people/volunteer_questionnaires_settings. There is also a "Request questionnaire
completion by a group of users" section on the settings form. This allows you to choose a
questionnaire and a user role. On submission of the settings form, all users who are members of the
selected role and have opted-in will have the selected questionnaire added to their requested
questionnaires list.

Questionnaire submissions can be viewed at Structure > Webforms > your questionnaire > Results.
There is a download tab allowing data to be exported.



