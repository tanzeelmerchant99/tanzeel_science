<?php
   /*
   Plugin Name:  Max Stats Table for WP Pro Quiz
   Plugin URI:  https://wordpress.org/plugins/max-stats-table-for-wp-pro-quiz/
   Description:  This plugin reqires the WP Pro Quiz plugin by Julius Fischer (https://wordpress.org/plugins/wp-pro-quiz/).  The plugin will create a page very similar to the "Statistics Overview" page already available for every test in WP Pro Quiz but it will display those statistics for every quiz at one time.
   Version:  3.2
   Original Author:  Jerod Karam
   Original Author URI:  n/a
   License: GLPL2
   */


/*	create a shortcode called [wppq-max-stats] so that users can insert this plugin into a page of thier choosing	*/
add_shortcode( 'wppq-max-stats', 'wppq_max_stats' );

/*	trigger the function which attaches the stylesheet	*/
add_action( 'wp_print_styles', 'wppq_register_stylesheet_for_plugin' );

/*	trigger the function which loads the javascript file	*/
add_action( 'wp_enqueue_scripts', 'wppq_js_init' );

/*	after Wordpress initializes, trigger the function which tests the POST variable and clears the database tables if necessary	*/
add_action( 'init', 'wppq_truncate_db_tables' );


function wppq_js_init() {

/*	load the javascript file	*/

	wp_enqueue_script( 'wppq_max_stats_js', plugins_url( 'wppq-javascript.js', __FILE__ ));
}	// close the javascript init function


function wppq_register_stylesheet_for_plugin() {

/*	Register the style sheet for this plugin.  Stylesheet is in main plugin directory and is named "wppq-style.css"	*/

	wp_register_style('wppq_max_stats_css', plugins_url('wppq-style.css', __FILE__));
	wp_enqueue_style('wppq_max_stats_css');
}	// close the register stylesheet function


function wppq_max_stats() {

/*	The meat and potatoes of the plugin.	*/

	/*	allow the function to use the global wordpress variable for database access	*/
	global $wpdb;
	
	/*	clear any possible contents from the final max stats output strings 	*/
	$wppq_ms_output = "";
	$wppq_ms_output_copyable = "";
	
	$wppq_blog_name = get_bloginfo( 'name' );
	
	/*	check to see if the URL variable "reset" is set and, if so, assign the value to db_reset_test for use in the subsequent switch / case statement.
		I know this is going to confuse me later so the next statement is called a "Ternary Operator" and is described here:
		http://php.net/manual/en/language.operators.comparison.php#language.operators.comparison.ternary	*/
	$db_reset_test = isset($_GET['reset']) ? $_GET['reset'] : '';
	$stats_tables_last_cleared = get_option( "wppq_statistics_tables_last_cleared", $default = false );
	switch ($db_reset_test) {
		case "success":
			$wppq_ms_output .= "<a href='?reset=reese'><div class='dbResetSuccessful'>All statistics have been cleared from the WP Pro Quiz tables.</div></a>";
			$wppq_ms_output .= "<div class='centerTheThingsDiv'><span class='statsLastCleared'>Statistics tables last cleared -- " . $stats_tables_last_cleared . "</span></div>";
			break;
		case "noadmin":
			$wppq_ms_output .= "<div class='dbResetNoAdmin'>Only site administrators can clear the database statistics.</div>";
			break;
		case "error":
			$wppq_ms_output .= "<div class='dbResetError'>An error occurred when trying to delete the statistics data.</div>";
			break;
		case "reese": // EASTER EGG!
			$wppq_ms_output .= "<a href='?reset=sasha'><div class='dbResetReese'>Reese is the GREATEST seven-year-old ever!</div></a>";
			break;
		case "sasha": // EASTER EGG!
			$wppq_ms_output .= "<div class='dbResetSasha'>...and Sasha is the GREATEST five-year-old ever!</div>";
			break;
	}	// close the switch statement

	/*	check to see if user is admin and, if so, display the "clear statistics" button.	*/
	if ( (current_user_can( 'manage_options' )) && ( !isset($_GET['reset']) ) && ( !is_admin() ) ) {
		?>
		<div class="centerTheThingsDiv">
			<form method="post" id="deleteAllStatistics" action="">
			<input type="submit" class="delStatsButton" name="deleteButtonWasPressed" onclick="return wppq_confirm_delete()" value="Delete All Statistics"></input><br />
			<span class="statsLastCleared">Statistics tables last cleared -- <?php echo $stats_tables_last_cleared; ?></span>
			</form>
		</div>
		<?php
	}

	/*	pull from the WP Pro Quiz Master table the test IDs, test names, statistics enabled (or not), whether or not there's a question limit, and what that question limit is per test 	*/
	$quiz_name_id = $wpdb->get_results( "SELECT id, name, statistics_on, show_max_question, show_max_question_value FROM ".$wpdb->prefix."wp_pro_quiz_master");

	/*	only continue doing stuff if the aforementioned data is available.  otherwise, output an error message (far below)	*/
	if(!empty($quiz_name_id))
	{

	/*	get the wordpress username and user ID from the users table.  not needed until later but placed outside the loop so it's only called once.  	*/
		$user_name_id = $wpdb->get_results( "SELECT id, user_login FROM ".$wpdb->prefix."users");
		
		$wppq_ms_output_copyable .= "<hr><hr><div class='wppqTestTitle'>Anonymous Test Results</div><div class='wppqTestSubTitle'>Click anywhere in the text box below & the statistics will automatically be copied to your clipboard.  Then you can paste the data into a spreadsheet.  Use the spreadsheet's \"text-to-column\" function with a double-colon ( :: ) but no spaces for separator.<br /><br />These are the column headings:<br />Today's Date :: Site Name :: Quiz Name :: Number of Questions in Quiz :: Number of Quizzes Taken :: Number of Correct Answers :: Number of Incorrect Answers :: Percent Correct</div><pre class='outputBox' id='selectableTOO' onclick='selectText(\"selectableTOO\")'>";

	/*	set up a loop to run through every quiz / test that is available in WP Pro Quiz 	*/
		foreach($quiz_name_id as $quiz_name_row)
		{
	/*	pull the number of questions on each test out of the DB and assign it to a variable	*/
			$num_questions_on_test = $quiz_name_row->show_max_question_value;

	/*	write the test name.  use the CSS formatting class "wppqTestTitle"	*/
			$wppq_ms_output .= "<div class='wppqTestTitle'>" . $quiz_name_row->name ."</div>";

/*
	This section pulls and aggregates the data for a non-registered user.  The user ID number in this section is
	hard-coded to "0" which is "anonymous" in WP Pro Quiz.  The next section pulls and aggregates data for
	registered users.
*/

	/*	if the test has statistics enabled, continue.  otherwise, output a "stats not enabled" message (see below)	*/
			if($quiz_name_row->statistics_on == 1)
			{

	/*	THE "query" variable holds the SQL string that is used to filter all data in the statistics tables and
		aggregate the POINTS VALUES for the current test in this loop	*/
				$points_query =	"SELECT sum(points) ".
										"FROM ".$wpdb->prefix."wp_pro_quiz_statistic ".
										"JOIN ".$wpdb->prefix."wp_pro_quiz_statistic_ref ".
											"ON ".$wpdb->prefix."wp_pro_quiz_statistic.statistic_ref_id = ".$wpdb->prefix."wp_pro_quiz_statistic_ref.statistic_ref_id ".
										"WHERE quiz_id = " . $quiz_name_row->id . " AND user_id = 0";
	/*	assign the actual NUMBER OF POINTS to the correct variable	*/
				$num_points = $wpdb->get_var($points_query);

	/*	THE "query" variable holds the SQL string that is used to filter all data in the statistics tables and
		aggregate the NUMBER OF CORRECT ANSWERS for the current test in this loop	*/
				$num_correct_query =	"SELECT sum(correct_count) ".
										"FROM ".$wpdb->prefix."wp_pro_quiz_statistic ".
										"JOIN ".$wpdb->prefix."wp_pro_quiz_statistic_ref ".
											"ON ".$wpdb->prefix."wp_pro_quiz_statistic.statistic_ref_id = ".$wpdb->prefix."wp_pro_quiz_statistic_ref.statistic_ref_id ".
										"WHERE quiz_id = " . $quiz_name_row->id . " AND user_id = 0";
	/*	assign the actual NUMBER OF CORRECT ANSWERS to the correct variable	*/
				$num_correct = $wpdb->get_var($num_correct_query);

	/*	THE "query" variable holds the SQL string that is used to filter all data in the statistics tables and
		aggregate the NUMBER OF INCORRECT ANSWERS for the current test in this loop	*/
				$num_incorrect_query =	"SELECT sum(incorrect_count) ".
										"FROM ".$wpdb->prefix."wp_pro_quiz_statistic ".
										"JOIN ".$wpdb->prefix."wp_pro_quiz_statistic_ref ".
											"ON ".$wpdb->prefix."wp_pro_quiz_statistic.statistic_ref_id = ".$wpdb->prefix."wp_pro_quiz_statistic_ref.statistic_ref_id ".
										"WHERE quiz_id = " . $quiz_name_row->id . " AND user_id = 0";
	/*	assign the actual NUMBER OF INCORRECT ANSWERS to the correct variable	*/
				$num_incorrect = $wpdb->get_var($num_incorrect_query);
				
	/*	if any of the variables are empty (i.e. no data) then assign zero to that variable instead of "blank."	*/
				if (empty($num_points)) {
					$num_points = 0;
				}
				if (empty($num_correct)) {
					$num_correct = 0;
				}
				if (empty($num_incorrect)) {
					$num_incorrect = 0;
				}
				if (($num_correct + $num_incorrect) == 0) {
					$percent_correct = 0;
					$tests_completed = 0;
				} else {
					$tests_completed = (($num_correct + $num_incorrect) / $num_questions_on_test);	// if there are correct or incorrect answers, calculate the number of tests taken.
					$percent_correct = ($num_correct / ($num_correct + $num_incorrect)) * 100;  // use the number of correct and number of incorrect answers to calculate a percentage correct answers and assign that to a variable.
				}

	/*	build up the output by forming a table, writing the header row, and writing the "Anonymous User" row with correct data
		If a test has a maximum number of questions, we will calculate and display the total number of tests taken instead of the total number of points scored.	*/
				$wppq_ms_output .=  "<table class='wppqMaxStatsTable'>";
				if ( $quiz_name_row->show_max_question == 1) {
					$wppq_ms_output .= "<thead><tr><th class='wppqWideField'>User Name</th><th class='wppqRightAlign'>Tests Completed</th><th class='wppqRightAlign'>Number Correct</th><th class='wppqRightAlign'>Number Incorrect</th><th class='wppqRightAlign'>Percent Correct</th></tr></thead>";
				} else {
					$wppq_ms_output .= "<thead><tr><th class='wppqWideField'>User Name</th><th class='wppqRightAlign'>Total Points</th><th class='wppqRightAlign'>Number Correct</th><th class='wppqRightAlign'>Number Incorrect</th><th class='wppqRightAlign'>Percent Correct</th></tr></thead>";
				}
				$wppq_ms_output .= "<tbody>";
				if ( $quiz_name_row->show_max_question == 1) {
					$wppq_ms_output .= "<tr><td>Anonymous</td><td class='wppqRightAlign'>" . number_format($tests_completed) . "</td><td class='wppqRightAlign'>" . number_format($num_correct) . "</td><td class='wppqRightAlign'>" . number_format($num_incorrect) . "</td><td class='wppqRightAlign'>" . number_format($percent_correct, 2) . "%</tr>";
				} else {
					$wppq_ms_output .= "<tr><td>Anonymous</td><td class='wppqRightAlign'>" . number_format($num_points) . "</td><td class='wppqRightAlign'>" . number_format($num_correct) . "</td><td class='wppqRightAlign'>" . number_format($num_incorrect) . "</td><td class='wppqRightAlign'>" . number_format($percent_correct, 2) . "%</tr>";
				}
				$wppq_ms_output_copyable .=  date("Y-M-d") . "::" . $wppq_blog_name . "::" . $quiz_name_row->name . "::" . $num_questions_on_test . "::" . number_format($tests_completed) . "::" . number_format($num_correct) . "::" . number_format($num_incorrect) . "::" . number_format($percent_correct, 2) . "%<br />";

/*
	This section pulls and aggregates the data for registered Wordpress users.
*/

	/*	start the loop which pulls and aggregates data for each resgistered Wordpress user	*/
				foreach($user_name_id as $user_name_row)
				{
	/*	THE "query" variable holds the SQL string that is used to filter all data in the statistics tables and
		aggregate the POINTS VALUES for the current test and current user in this loop	*/
					$points_query =	"SELECT sum(points) ".
											"FROM ".$wpdb->prefix."wp_pro_quiz_statistic ".
											"JOIN ".$wpdb->prefix."wp_pro_quiz_statistic_ref ".
												"ON ".$wpdb->prefix."wp_pro_quiz_statistic.statistic_ref_id = ".$wpdb->prefix."wp_pro_quiz_statistic_ref.statistic_ref_id ".
											"WHERE quiz_id = " . $quiz_name_row->id . " AND user_id = " . $user_name_row->id;
	/*	assign the actual NUMBER OF POINTS for this user to the correct variable	*/
					$num_points = $wpdb->get_var($points_query);

	/*	THE "query" variable holds the SQL string that is used to filter all data in the statistics tables and
		aggregate the NUMBER OF CORRECT ANSWERS for the current test and current user in this loop	*/
					$num_correct_query =	"SELECT sum(correct_count) ".
											"FROM ".$wpdb->prefix."wp_pro_quiz_statistic ".
											"JOIN ".$wpdb->prefix."wp_pro_quiz_statistic_ref ".
												"ON ".$wpdb->prefix."wp_pro_quiz_statistic.statistic_ref_id = ".$wpdb->prefix."wp_pro_quiz_statistic_ref.statistic_ref_id ".
											"WHERE quiz_id = " . $quiz_name_row->id . " AND user_id = " . $user_name_row->id;
	/*	assign the actual NUMBER OF CORRECT ANSWERS for this user to the correct variable	*/
					$num_correct = $wpdb->get_var($num_correct_query);

	/*	THE "query" variable holds the SQL string that is used to filter all data in the statistics tables and
		aggregate the NUMBER OF INCORRECT ANSWERS for the current test and current user in this loop	*/
					$num_incorrect_query =	"SELECT sum(incorrect_count) ".
											"FROM ".$wpdb->prefix."wp_pro_quiz_statistic ".
											"JOIN ".$wpdb->prefix."wp_pro_quiz_statistic_ref ".
												"ON ".$wpdb->prefix."wp_pro_quiz_statistic.statistic_ref_id = ".$wpdb->prefix."wp_pro_quiz_statistic_ref.statistic_ref_id ".
											"WHERE quiz_id = " . $quiz_name_row->id . " AND user_id = " . $user_name_row->id;
	/*	assign the actual NUMBER OF INCORRECT ANSWERS for this user to the correct variable	*/
					$num_incorrect = $wpdb->get_var($num_incorrect_query);

	/*	if any of the variables are empty (i.e. no data) then assign zero to that variable instead of "blank."	*/
					if (empty($num_points)) {
						$num_points = 0;
					}
					if (empty($num_correct)) {
						$num_correct = 0;
					}
					if (empty($num_incorrect)) {
						$num_incorrect = 0;
					}
					if (($num_correct + $num_incorrect) == 0) {
						$percent_correct = 0;
						$tests_completed = 0;
					} else {
						$tests_completed = (($num_correct + $num_incorrect) / $num_questions_on_test);	// if there are correct or incorrect answers, calculate the number of tests taken.
						$percent_correct = ($num_correct / ($num_correct + $num_incorrect)) * 100;
					}

	/*	add a row to the output table for this user with the user's personal statistics for this test
		If a test has a maximum number of questions, we will calculate and display the total number of tests taken instead of the total number of points scored.	*/
					if ( $quiz_name_row->show_max_question == 1) {
						$wppq_ms_output .= "<tr><td>" . $user_name_row->user_login . "</td><td class='wppqRightAlign'>" . number_format($tests_completed) . "</td><td class='wppqRightAlign'>" . number_format($num_correct) . "</td><td class='wppqRightAlign'>" . number_format($num_incorrect) . "</td><td class='wppqRightAlign'>" . number_format($percent_correct, 2) . "%</tr>";
					} else {
						$wppq_ms_output .= "<tr><td>" . $user_name_row->user_login . "</td><td class='wppqRightAlign'>" . number_format($num_points) . "</td><td class='wppqRightAlign'>" . number_format($num_correct) . "</td><td class='wppqRightAlign'>" . number_format($num_incorrect) . "</td><td class='wppqRightAlign'>" . number_format($percent_correct, 2) . "%</tr>";
					}
				}	// close the user loop
			}	// close the main "statistics enabled?" test

	/*	inform the user if statistics are not enabled for a particular test	*/
			else
			{
				$wppq_ms_output .= "<tr><td colspan='5'>Statistics not enabled for this test.</td></tr>";
			}	// close the "else" portion of the test for "statistics enabled?"

	/*	close the table cleanly	*/
			$wppq_ms_output .= "</tbody>";
			$wppq_ms_output .= "</table>"; 
		}	// close the all tests loop

	} 	// close the "do stuff" part of the test for WP Pro Quiz Master Table
	else {
	/*	if the plugin cannot find the WP Pro Quiz master table, output an error	*/
		$wppq_ms_output = "<div class='wppqError'>Ooops!  It appears that something went wrong.  The plugin cannot find the <em><strong>wp-pro-quiz-master</strong></em> table.<br />Please ensure that the WP Pro Quiz plugin is installed and activated.</div>";
	}	// close the "output an error" part of the test for WP Pro Quiz Master Table 

	/*	return all the text to be displayed to the user.	*/

	$wppq_ms_output_copyable .= "</pre>";
	$wppq_ms_output .= "<br /><br />" . $wppq_ms_output_copyable;

	return $wppq_ms_output;
}	// close the function wppq_max_stats


function wppq_truncate_db_tables() {
	global $wpdb;

	if (isset($_POST['deleteButtonWasPressed'])) {
		if ( (current_user_can( 'manage_options' ))) {

			$table_one = $wpdb->prefix . "wp_pro_quiz_statistic";
			$do_the_delete = $wpdb->query("TRUNCATE TABLE $table_one");

			$table_two = $wpdb->prefix . "wp_pro_quiz_statistic_ref";
			$do_the_delete = $wpdb->query("TRUNCATE TABLE $table_two");
			
			$wppq_option_name = "wppq_statistics_tables_last_cleared";
			$wppq_option_value = current_time('mysql');

			update_option( $wppq_option_name, $wppq_option_value );


			if ( $wpdb->last_error !== '' ) {

/*
		        $str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
		        $query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );
		        
		        echo $str . "<br />";
		        echo $query;
		        die();
*/		        
				header( 'Location: ?reset=error' ) ;		        

		    } else {
		    
				header( 'Location: ?reset=success' ) ;
		    
		    }	// close the "if" for error catching


		} else {

			header( 'Location: ?reset=noadmin' ) ;

		}	//  close test for site administrator

	}	// close "if" test for POST variable to detect delete command
	
}	// close function truncate db tables

?>
