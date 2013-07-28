quiz-prereq-extension
=====================

Extension for the WordPress 'WP Survey And Quiz Tool' plugin allowing quizzes and pages/posts to have prerequisite quizzes.


How to use
=====================

1. Clone this repository, and copy the 'wp-survey-and-quiz-tool-prerequisite-extension' directory and its contents into the 'plugins' directory within your WordPress source code ({yourWordPressRepo}/php/wp-content/plugins).
2. Once you have committed and pushed those changes, activate the extension plugin through the WordPress administration dashboard's Plugins page.
3. This plugin assumes all users will be logged in. You can enforce this with the Login Configurator plugin (http://wordpress.org/plugins/login-configurator/).
4. When creating or editing a quiz with WPSQT, you will now see a Prerequisite Quiz option and a menu populated with the quizzes you have created. To make one quiz a prerequisite of another, select the prerequisite in this menu and save the changes. When a WordPress page contains a quiz for which the logged-in user has not completed the prerequisite, the page content will not load; a message will be displayed listing the unfulfilled prerequisite.
5. To specify that the content of a page that does not contain a quiz should not be display unless a particular quiz has been completed, include a shortcode in the page in the format:
```[wpsqt name="QuizName" type="prereq"]``` 
