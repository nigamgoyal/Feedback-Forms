<?php
// Callback function for give feedback menu page
function employee_feedback_page()
{
    ob_start(); // Start output buffering 
    if (isset($_POST['submitfeedback']) && $_POST['submitfeedback'] == 'Share Feedback') {
        $empid = $_POST['empid'] ?? '';
        $empname = $_POST['empname'] ?? '';
        $feedbackmsg = $_POST['feedbackmesg'] ?? '';
        $suggestedmsg = $_POST['suggestedmsg'] ?? array();
        $suggestedmsg_str = implode('|', $suggestedmsg);

        $nameerror = (empty($empid) || $empname === '') ? "Please select employee name." : '';
        $msgerror = empty($feedbackmsg) ? "Please give feedback message." : '';

        if (empty($nameerror) && empty($msgerror)) {
            // Validate and sanitize user input here
            $empid = intval(sanitize_text_field($_POST['empid']));
            $empname = sanitize_text_field($empname);
            $feedbackmsg = htmlspecialchars($_POST['feedbackmesg'], ENT_QUOTES);

            // Insert data into table
            global $wpdb;
            $table_name = $wpdb->prefix . 'feedback';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                echo "<p class='alert alert-danger'>Oops! Table does not found, so feedbacks cannot be inserted!</p>";
            } else {
                $current_user = wp_get_current_user();
                $userID = get_user_by('id', $empid);
                if ($userID && $userID->display_name === $empname) {
                    $data = array(
                        'sent_to_userid' => $empid,
                        'sent_to_username' => $empname,
                        'feedback_message' => $feedbackmsg,
                        'suggested_message' => $suggestedmsg_str,
                        'sent_by_userid' => $current_user->ID,
                        'sent_by_username' => $current_user->display_name,
                    );

                    $result = $wpdb->insert($table_name, $data);

                    if ($result === false) {
                        // Log the SQL error
                        error_log("Error inserting feedback into database: " . $wpdb->last_error);
                    } else {
                        // Check if user is logged in
                        // if (is_user_logged_in()) {
                        //     // User is logged in, display link to the WordPress admin
                        //     $link = esc_url(home_url('/wp-admin'));
                        // } else {
                        //     // User is not logged in, display link to the login page
                        //     $link = esc_url(home_url('/login'));
                        // }

                        // Send email to receiver
                        $to = $userID->user_email;
                        $subject = 'You have received a feedback';
                        $message = 'Hi ' . userFirstname($userID->display_name) . ',</br></br>You have received feedback from ' . capitalizeWords($current_user->display_name) . '. Please login to employee portal to view your feedback.</br>
                        <a href="' . esc_url(home_url('/login')) . '">Click Here</a> </br></br>Thank You';
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        wp_mail($to, $subject, $message, $headers);

                        // Display a success message to the user
                        echo "<p class='alert alert-success'>Thank you for your feedback!</p>";
                    }
                } else echo "<p class='alert alert-danger'>Oops! User not found!</p>";
            }
        }
    }

    if (isset($_POST['sendrequest']) && $_POST['sendrequest'] == 'Send') {
        $requestedEmpId = $_POST['requestedEmpId'] ?? '';
        $requestedEmpName = $_POST['requestedEmpName'] ?? '';

        $requestedNameerror = (empty($requestedEmpId) || $requestedEmpName === '') ? "Please select employee name." : '';
        if (empty($requestedNameerror)) {
            $current_user = wp_get_current_user();
            $userID = get_user_by('id', $requestedEmpId);
            $to = $userID->user_email;
            $subject = 'You have requested for feedback';
            $message = 'Hi ' . userFirstname($userID->display_name) . ',</br></br>'
            .capitalizeWords($current_user->display_name) . ' has requested feedback. We would appreciate it if you could spare some time to provide '.capitalizeWords($current_user->display_name) .' with feedback.</br></br> Please login to employee portal to give feedback.</br>
                        <a href="' . esc_url(home_url('/login')) . '">Click Here</a> </br></br>Thank You';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);

            // Display a success message to the user
            echo "<p class='alert alert-success'>Your feedback request sent!</p>";
        } 
        else echo "<p class='alert alert-danger'>Oops! User not found!</p>";
    }

    $users = get_users(array('role__in' => array('employee'))); ?>
    <!-- form HTML goes here -->
    <div class="give_feedback_container feedback_container">
        <h3>Feedback</h3>
        <div class="feedback-btns-row">
            <button type="button" id="give_feedback_button" onclick="showHideData('feedback-form','request-feedback');" class="give-feedback feedbackbtn">Give Feedback</button>
            <button type="button" id="request_feedback_button" class="request-feedback feedbackbtn" onclick="showHideData('request-feedback','feedback-form');">Request Feedback</button>
        </div>
        <div id="feedback-form" class="feedback-form" style="display: block;">
            <form method="POST">
                <div class="mb-3 mt-3">
                    <label for="about" class="form-label fw-bolder">Who's the feedback about?</label>
                    <select class="form-control" name="empid" id="empid" onclick="addNametoInputHidden('#empid', '#empname');" required>
                        <option value="" disabled selected hidden>Select their names</option>
                        <?php foreach ($users as $user) {  ?>
                            <option value="<?php echo esc_attr($user->ID) ?>" data-display-name="<?php echo esc_attr($user->display_name) ?>"><?php echo esc_html($user->display_name) ?></option>
                        <?php } ?>
                    </select>
                    <input type="hidden" name="empname" id="empname" value="">
                    <p class="text-danger"> <?php if (isset($nameerror)) echo $nameerror; ?> </p>
                </div>
                <div class="mb-3 mt-3">
                    <label for="about" class="form-label fw-bolder">What's your feedback?</label>
                    <textarea class="form-control" name="feedbackmesg" id="empfeedback" rows="4" placeholder="Write some feedback..." required></textarea>
                    <p class="text-danger"> <?php if (isset($msgerror)) echo $msgerror; ?> </p>
                </div>
                <div class="mb-3 mt-3">
                    <label for="values" class="form-label fw-bolder">Which values did they embody?</label>
                    <div>
                        <label class="badge rounded-pill PillList-item">
                            <input type="checkbox" name="suggestedmsg[]" value="Embody a service mindset">
                            <span class="PillList-label">Embody a service mindset
                                <span class="Icon Icon--checkLight Icon--smallest"><i class="fa fa-check"></i></span>
                            </span>
                        </label>
                        <label class="badge rounded-pill PillList-item">
                            <input type="checkbox" name="suggestedmsg[]" value="Dream big, then make it real">
                            <span class="PillList-label">Dream big, then make it real
                                <span class="Icon Icon--checkLight Icon--smallest"><i class="fa fa-check"></i></span>
                            </span>
                        </label>
                        <label class="badge rounded-pill PillList-item">
                            <input type="checkbox" name="suggestedmsg[]" value="Be proud of the how">
                            <span class="PillList-label">Be proud of the how
                                <span class="Icon Icon--checkLight Icon--smallest"><i class="fa fa-check"></i></span>
                            </span>
                        </label>
                        <label class="badge rounded-pill PillList-item">
                            <input type="checkbox" name="suggestedmsg[]" value="Embrace an ownership mentality">
                            <span class="PillList-label">Embrace an ownership mentality
                                <span class="Icon Icon--checkLight Icon--smallest"><i class="fa fa-check"></i></span>
                            </span>
                        </label>
                    </div>
                </div>
                <input type="submit" class="button button-primary float-end share" name="submitfeedback" value="Share Feedback">
            </form>
        </div>
        <div class="request-feedback" id="request-feedback" style="display: none;">
            <form method="POST">
                <div class="mb-3 mt-3">
                    <label for="about" class="form-label fw-bolder">Whom would you like to request feedback from?</label>
                    <select class="form-control" name="requestedEmpId" id="requestedEmpId" onclick="addNametoInputHidden('#requestedEmpId', '#requestedEmpName');" required>
                        <option value="" disabled selected hidden>Select their names</option>
                        <?php foreach ($users as $user) {  ?>
                            <option value="<?php echo esc_attr($user->ID) ?>" data-display-name="<?php echo esc_attr($user->display_name) ?>"><?php echo esc_html($user->display_name) ?></option>
                        <?php } ?>
                    </select>
                    <input type="hidden" name="requestedEmpName" id="requestedEmpName" value="">
                    <p class="text-danger"> <?php if (isset($requestedNameerror)) echo $requestedNameerror; ?> </p>
                </div>
                <input type="submit" class="button button-primary float-end send" name="sendrequest" value="Send">
            </form>
        </div>
    </div>
<?php
    ob_end_flush(); // End output buffering and send output 
}
?>