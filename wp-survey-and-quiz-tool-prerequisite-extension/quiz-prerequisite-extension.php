<?php
/*
Plugin Name: WP Survey And Quiz Tool Prerequisite Extension
Plugin URI: http://www.github.com/codemiller/quiz-prereq-extension
Description: Extension for WP Survey And Quiz Tool that allows users to select prerequisite quizzes that must have been completed before a page containing a quiz will load.
Author: Katie Miller
Author URI: http://codemiller.com
Version: 1.0

WP Survey And Quiz Tool Prerequisite Extension
Copyright (C) 2013 Katie Miller 

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your H) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

add_filter("wpsqt_form_quiz", "filter_quiz_form");
add_filter("the_content", "filter_content_for_quiz_prereqs", 0);
add_filter("wpsqt_prereq_error", "handle_prereq_error");

function filter_quiz_form($form) {
    $quizResults = Wpsqt_System::getAllItemDetails('quiz');
    $quizzes = array("none");
    foreach($quizResults as $quiz) {
       $quizId = $quiz['id'];
       $quizName = $quiz['name'];
       array_push($quizzes, $quizId.": ".$quizName);
    }  
    $form->addOption("wpsqt_prerequisite", "Prerequisite Quiz", "select", $options['prerequisite'] , "Select a prerequisite quiz which much be completed successfully before this quiz can be attempted.", $quizzes );
}

function filter_content_for_quiz_prereqs($content) {
    # Only filter if user is logged in
    if (0 == wp_get_current_user()->ID) {
        return $content;
    }
    $personName = wp_get_current_user()->user_login; 
    $missing_prereqs = array();
    $prereq_matches = array();
    # Filter based on any explicit prerequisites first
    if (preg_match('/.*\[wpsqt name="(.+)" type="prereq"\].*/', $content, $prereq_matches) === 1) {
        foreach(array_slice($prereq_matches, 1) as $prereqName) {	           
	    $quizDetails = Wpsqt_System::getItemDetails($prereqName, 'quiz');
            if ($quizDetails && $quizDetails['id']) {
	    	$allowed = get_quiz_result_for_person($personName, $quizDetails['id']);
            	if (! $allowed) {
                    array_push($missing_prereqs, $prereqName);
                }	
            }    
        }
    }
    $quiz_matches = array();
    if (preg_match('/.*\[wpsqt name="(.+)" type="quiz"\].*/', $content, $quiz_matches) === 1) {
        foreach(array_slice($quiz_matches, 1) as $quizNameMatch) {      
            $quizDetails = Wpsqt_System::getItemDetails($quizNameMatch, 'quiz'); 
            if ($quizDetails && $quizDetails['prerequisite']) {
                $quizPrereq = $quizDetails['prerequisite'];
                if ($quizPrereq == "none") {
                    break; 
                }
                $quizPrereqArray = split(":", $quizPrereq, 2);
                $quizId = $quizPrereqArray[0];
                $quizName = trim($quizPrereqArray[1]);
                $allowed = get_quiz_result_for_person($personName, $quizId); 
                if (! $allowed) {
                    array_push($missing_prereqs, $quizName);
                }
            }
        }
    }
    if (count($missing_prereqs) > 0) {
        $error = "<strong>Sorry, you cannot view this content until you have completed the following quizzes: <ul>";
        foreach($missing_prereqs as $missing) {
            $error = $error."<li>".$missing."</li>";
        }
        $error = $error."</ul></strong>";
        return $error;
    }  
    return $content;
}

function get_quiz_result_for_person($personName, $quizId) {
    global $wpdb;

    $resultRow = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM ".WPSQT_TABLE_RESULTS." WHERE person_name = %p AND item_id = %i AND pass = 1",
            array($personName, $quizId))
        , ARRAY_A
    );
    return !(empty($resultRow));
}

# Remove error message for prereq short code when prereqs met
function handle_prereq_error($message, $errors) {
    return "";
}

