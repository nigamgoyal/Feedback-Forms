<?php

if (isset($_GET['action']) && ($_GET['action'] === "fetchTemplateDetails") && !empty($_GET['templateId'])) {

    $templateId = $_GET['templateId'];
    global $wpdb;
    $existing_record = $wpdb->get_row(
        "SELECT t.template_id, t.template_name, t.template_category, t.template_description, m.sent_to_username, m.frequency, m.date_time, m.meeting_status
                FROM wp_feedback_1_to_1_meeting_template AS t
                LEFT JOIN  wp_feedback_1_to_1_meeting AS m ON t.template_id = m.template_id
                WHERE t.template_id = '{$templateId}'"
    );

    $existingAgenda = $wpdb->get_results("SELECT agenda_id, talking_point, agenda_status from wp_feedback_1_to_1_meeting_agenda WHERE template_id = '{$templateId}'");

    $existingAction = $wpdb->get_results("SELECT action_id, action_item, action_status from wp_feedback_1_to_1_meeting_actionitem WHERE template_id = '{$templateId}'");

    if ($existing_record) {
        $existing_data = array();
        // $existing_template_id = $existing_record->template_id,
        if (!empty($existing_record->sent_to_username)) {
            $existing_template_username = '<p><span class="card-usernames one_to_oneby me-2">' . initials($existing_record->sent_to_username) . '</span>' . capitalizeWords($existing_record->sent_to_username) . '</p>';
            $existing_data[] = $existing_template_username;
        }
        if (!empty($existing_record->date_time) || !empty($existing_record->meeting_status || !empty($existing_record->frequency))) {
            $existing_template_date_status = '<p>' . dateFormatfor1to1($existing_record->date_time) . '<span class="ms-5">Frequency: ' . $existing_record->frequency . '</span><span class="float-end" id="meetingStatus">' . $existing_record->meeting_status . '</span></p>';
            $existing_data[] = $existing_template_date_status;
        }
        // if(!empty($existing_record->meeting_status)){
        //     // $existing_template_status = '<p> <span class="float-end" id="meetingStatus"> Status: '. $existing_record->meeting_status .'</span></p>';
        //     $existing_data[] = $existing_record->meeting_status;
        // }
        if (!empty($existing_record->template_name)) {
            $existing_template_name = '<p> Topic: ' . $existing_record->template_name . '</p>';
            $existing_data[] = $existing_template_name;
        }
        if (!empty($existing_record->template_category)) {
            $existing_template_category = '<p> Category: ' . $existing_record->template_category . '</p>';
            $existing_data[] = $existing_template_category;
        }
        if (!empty($existing_record->template_description)) {
            $existing_template_description = '<textarea>' . $existing_record->template_description . '</textarea>';
            $existing_data[] = $existing_template_description;
        } else {
            $existing_template_emptydescription = '<textarea></textarea>';
            $existing_data[] = $existing_template_emptydescription;
        }
        if (!empty($existingAgenda)) {
            $existing_data[] = $existingAgenda;
        }
        if (!empty($existingAction)) {
            $existing_data[] = $existingAction;
        }
        echo json_encode(array("record" => $existing_data, "message" => 'Success'));
        exit;
    } else {
        echo json_encode(array("record" => 'Not found', "message" => 'No existing record found :('));
        exit;
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'existingTemplate') {
    if (!isset($_POST['meetingTemplateNewData'])) {
        echo json_encode(array("result" => "fail"));
        exit;
    }
    $dataArr = json_decode($_POST['meetingTemplateNewData'], true);

    if (empty($dataArr) || !is_array($dataArr) || !array_key_exists('templateId', $dataArr) || !array_key_exists('agenda', $dataArr) || !array_key_exists('action_item', $dataArr)) {
        echo json_encode(array("result" => "fail", "message" => "Invalid Data"));
        exit;
    }



    // if($dataArr['userStatus'] !== 'on'){
    //     echo json_encode(array("status" => "off", "message" => "Invalid Data"));
    //     exit;
    // }
    $meetingStatus = $dataArr['userStatus'];

    $templateId = $dataArr['templateId'];

    if (!empty($meetingStatus)) {
        updateMeetingStatus($templateId, $meetingStatus);
    }

    if (!empty($dataArr['description']) || $dataArr['description'] == '') {
        $description = $dataArr['description'];
        updateTemplateDescription($templateId, $description);
    }

    if (!empty($dataArr['agenda'])) {
        // $newAgenda = json_encode($dataArr['agenda']);
        $newAgendas = $dataArr['agenda'];
        foreach ($newAgendas as $newAgenda) {
            addAgendaItem($templateId, $newAgenda);
        }
    }

    if (isset($dataArr['agenda_id']) && !empty($dataArr['agenda_id'])) {
        $agendaIds = $dataArr['agenda_id'];
        $agendaStatus = $dataArr['agenda_status'];
        if (!empty($templateId) && !empty($agendaIds) && $agendaStatus === 'off') {
            foreach ($agendaIds as $agendaId) {
                inactiveAgendaItem($templateId, $agendaId, $agendaStatus);
            }
        }
    }

    $actionStatus = $dataArr['action_status'];
    if (!empty($dataArr['action_item'])) {
        // $newActionItem = json_encode($dataArr['action_item']);
        $newActionItems = $dataArr['action_item'];
        foreach ($newActionItems as $newActionItem) {
            addActionItem($templateId, $newActionItem);
        }
    }

    if (isset($dataArr['action_id']) && !empty($dataArr['action_id'])) {
        $actionIds = $dataArr['action_id'];
        $actionStatus = $dataArr['action_status'];
        if (!empty($templateId) && !empty($actionIds) && $actionStatus === 'off') {
            foreach ($actionIds as $actionId) {
                inactiveActionItem($templateId, $actionId, $actionStatus);
            }
        }
    }

    exit;
}

function addMeetingTemplate($name, $category = '', $description = '')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_template';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return "<p class='alert alert-danger'>Oops! Table does not found, so data cannot be inserted!</p>";
    } else {
        $data = array(
            'template_name' => $name,
            'template_category' => $category,
            'template_description' => $description
        );

        $result = $wpdb->insert($table_name, $data);
        if ($result === false) {
            // Log the SQL error
            error_log("Error inserting data into database: " . $wpdb->last_error);
        }
    }
    return $wpdb->insert_id;
}

function updateTemplateDescription($templateID = '', $description = '')
{
    if (empty($templateID)) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_template';
    $existing_record = $wpdb->get_row($wpdb->prepare("SELECT template_description FROM $table_name WHERE template_id='{$templateID}'"));
    if ($existing_record) {
        // $updatedDescription = $existing_record->template_description . $description;

        $data = array(
            'template_description' => $description
        );

        $where = array(
            'template_id' => $templateID
        );
        $updateResult = $wpdb->update($table_name, $data, $where);
        if ($updateResult === false) {
            // Log the SQL error
            error_log("Error inserting data into database: " . $wpdb->last_error);
        } else {
            // Display a success message to the user
            echo 'Description updated!';
        }
    }
}


function addAgendaItem($templateID = '', $agenda = '')
{
    if (empty($templateID)) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_agenda';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return "<p class='alert alert-danger'>Oops! Table does not found, so data cannot be inserted!</p>";
    } else {
        if (empty($templateID) || empty($agenda)) {
            return false;
        }
        // insert
        $data = array(
            'template_id'       => $templateID,
            'talking_point'     => $agenda,
            'agenda_status'     => 'on'
        );

        $result = $wpdb->insert($table_name, $data);
        if ($result === false) {
            // Log the SQL error
            error_log("Error inserting data into database: " . $wpdb->last_error);
        } else {
            return "<p class='alert alert-success'>Agenda successfully submited!</p>";
        }
    }
}

function inactiveAgendaItem($templateID = '', $agendaId = '', $agendaStatus = '')
{
    if (empty($templateID) && empty($agendaId)) {
        return false;
    }
    if (!empty($agendaStatus) && $agendaStatus === 'off') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_agenda';
        $existing_record = $wpdb->get_row($wpdb->prepare("SELECT template_id, talking_point, agenda_status FROM $table_name WHERE agenda_id='{$agendaId}'"));
        if ($existing_record) {

            $data = array(
                'agenda_status' => $agendaStatus
            );

            $where = array(
                'agenda_id' => $agendaId
            );
            $updateResult = $wpdb->update($table_name, $data, $where);
            if ($updateResult === false) {
                // Log the SQL error
                error_log("Error inserting data into database: " . $wpdb->last_error);
            } else {
                // Display a success message to the user
                echo 'Agenda status updated!';
            }
        }
        // global $wpdb;
        // $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_agenda';
        // $wpdb->delete(
        //     $table_name,
        //     array('agenda_id' => $agendaId),
        //     array('%d')
        // );
        // return "Inactive successfully!";
    } else return "something went wrong";
}

function inactiveActionItem($templateID = '', $actionId = '', $actionStatus = '')
{
    if (empty($templateID) && empty($actionId)) {
        return false;
    }
    if (!empty($actionStatus) && $actionStatus === 'off') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_actionitem';
        $existing_record = $wpdb->get_row($wpdb->prepare("SELECT template_id, action_item, action_status FROM $table_name WHERE action_id='{$actionId}'"));
        if ($existing_record) {

            $data = array(
                'action_status' => $actionStatus
            );

            $where = array(
                'action_id' => $actionId
            );
            $updateResult = $wpdb->update($table_name, $data, $where);
            if ($updateResult === false) {
                // Log the SQL error
                error_log("Error inserting data into database: " . $wpdb->last_error);
            } else {
                // Display a success message to the user
                echo 'Action status updated!';
            }
        }
    } else return "something went wrong";
}

function addActionItem($templateID = '', $action = '')
{
    if (empty($templateID)) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting_actionitem';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return "<p class='alert alert-danger'>Oops! Table does not found, so data cannot be inserted!</p>";
    } else {
        if (empty($templateID) || empty($action)) {
            return false;
        }
        // insert
        $data = array(
            'template_id'       => $templateID,
            'action_item'       => $action
        );

        $result = $wpdb->insert($table_name, $data);
        if ($result === false) {
            // Log the SQL error
            error_log("Error inserting data into database: " . $wpdb->last_error);
        } else {
            return "<p class='alert alert-success'>Action successfully submited!</p>";
        }
    }
}

function updateMeetingStatus($templateID = '', $meetingStatus = '')
{
    if (empty($templateID) && empty($meetingStatus)) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting';
    $existing_record = $wpdb->get_row($wpdb->prepare("SELECT meeting_status FROM $table_name WHERE template_id='{$templateID}'"));
    if ($existing_record) {

        $data = array(
            'meeting_status' => $meetingStatus
        );

        $where = array(
            'template_id' => $templateID
        );
        $updateResult = $wpdb->update($table_name, $data, $where);
        if ($updateResult === false) {
            // Log the SQL error
            error_log("Error inserting data into database: " . $wpdb->last_error);
        } else {
            // Display a success message to the user
            echo 'Meeting status updated!';
        }
    }
}

function employees_1_to_1_callback()
{
    ob_start(); // Start output buffering 
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting';
    $current_user = wp_get_current_user();
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name)
        echo "<div class='alert alert-danger viewFeedback'>Table does not found, so 1:1s cannot be retrieved!</div>";

    else {

        $resultsPerPage = 10;

        // Count total number of records
        $totalRecords = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE sent_to_userid = '{$current_user->ID}' OR sent_by_userid = '{$current_user->ID}'");

        // Calculate the total number of pages
        $totalPages = ceil($totalRecords / $resultsPerPage);

        // Get the current page number
        $pageNumber = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $startIndex = max(0, ($pageNumber - 1) * $resultsPerPage);
        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE sent_to_userid = '{$current_user->ID}' OR sent_by_userid = '{$current_user->ID}' ORDER BY created_date DESC LIMIT $startIndex, $resultsPerPage");
    }

    if (isset($_POST['submit_1to1']) && $_POST['submit_1to1'] == 'Save') {

        $templateID = '';
        if (!empty($_POST['meetingTemplateData'])) {
            $dataArr = json_decode(base64_decode($_POST['meetingTemplateData']), true);
            $topicName = $dataArr['name'];
            $category = $dataArr['category'];
            $description = $dataArr['description'];
            $templateID = addMeetingTemplate($topicName, $category, $description); //templateID

            if (!empty($dataArr['agenda'])) {
                // $agenda = json_encode($dataArr['agenda']);
                $agenda = $dataArr['agenda'];
                foreach ($agenda as $talkingPoint) {
                    addAgendaItem($templateID, $talkingPoint);
                }
            }

            if (!empty($dataArr['action_item'])) {
                // $actionItem = json_encode($dataArr['action_item']);
                $actionItems = $dataArr['action_item'];
                foreach ($actionItems as $actionItem) {
                    addActionItem($templateID, $actionItem);
                }
            }
        }

        $empid = $_POST['emp1to1names'] ?? '';
        $empname = $_POST['emp1to1name'] ?? '';
        $meetingFrequency = $_POST['meetingFrequency'] ?? '';
        $meetingDate = $_POST['meetingDate'] ?? '';
        $meetingTime = $_POST['meetingTime'] ?? '';
        $status = $_POST['status'] ?? '';

        $nameerror = (empty($empid) || $empname === '') ? "Please select employee name" : '';
        $meetingFrequencyError = empty($meetingFrequency) ? "Please select meeting frequency" : '';
        $meetingDateError = empty($meetingDate) ? "Please select meeting date" : '';
        $meetingTimeError = empty($meetingTime) ? "Please select meeting time" : '';
        $templateError = empty($templateID) ? "Please select template" : '';
        $status = empty($status) ? "OFF" : $status;

        $currentDate = date('Y-m-d');
        if (!empty($meetingDate) && $meetingDate < $currentDate) {
            $validDate = "Please enter valid date";
        } else if ($templateID != "" && empty($nameerror) && empty($meetingFrequencyError) && empty($meetingDateError) && empty($meetingTimeError) && empty($templateError)) {
            // Validate and sanitize user input here
            if ($status == 'OFF' || $status == 'ON') {
                $empid = intval(sanitize_text_field($empid));
                $empname = sanitize_text_field($empname);
                $meetingFrequency = sanitize_text_field($meetingFrequency);
                $meetingDate = sanitize_text_field($meetingDate);
                $meetingTime = sanitize_text_field($meetingTime);
                $meetingDateTime = $meetingDate . ' ' . $meetingTime;
                $status = sanitize_text_field($status);

                // Insert data into table
                global $wpdb;
                $table_name = $wpdb->prefix . 'feedback_1_to_1_meeting';
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                    echo "<p class='alert alert-danger'>Oops! Table does not found, so data cannot be inserted!</p>";
                } else {
                    $current_user = wp_get_current_user();
                    $userID = get_user_by('id', $empid);
                    if ($userID && $userID->display_name === $empname) {
                        $data = array(
                            'template_id'       => $templateID,
                            'sent_to_userid'    => $empid,
                            'sent_to_username'  => $empname,
                            'sent_by_userid'    => $current_user->ID,
                            'sent_by_username'  => $current_user->display_name,
                            'frequency'         => $meetingFrequency,
                            'date_time'         => $meetingDateTime,
                            'meeting_status'    => $status,
                        );

                        $result = $wpdb->insert($table_name, $data);

                        if ($result === false) {
                            // Log the SQL error
                            error_log("Error inserting data into database: " . $wpdb->last_error);
                        } else {
                            // Send email to receiver
                            $to = $userID->user_email;
                            $subject = 'There is 1:1 scheduled for you';
                            $message = 'Hi ' . userFirstname($userID->display_name) . ',</br>' . capitalizeWords($current_user->display_name) . ' has scheduled 1:1 meeting with you.</br></br>Thank You.';
                            $headers = array(
                                'Content-Type: text/html; charset=UTF-8',
                                'Cc: ' . $current_user->user_email
                            );
                            wp_mail($to, $subject, $message, $headers);

                            // Display a success message to the user
                            echo "<p class='alert alert-success'>Sent your request for 1:1!</p>";
                            echo '<script>window.location.href = window.location.href;</script>';
                            exit;
                        }
                    } else echo "<p class='alert alert-danger'>Oops! User not found!</p>";
                }
            }
        } else echo '<p class="text-danger">Required fields are empty :(</p>';
    }
    $users = get_users(array('role__in' => array('administrator', 'employee')));
?>
    <!-- form HTML goes here -->
    <div class="feedback_container employees_1to1">
        <h3 class="mb-4">Set up 1:1 Relationship</h3>
        <button class="button button-primary oneTo1Btn" onclick="showHideData('result1to1','1to1_relationship');">View 1:1</button>
        <button class="button button-primary oneTo1Btn" id="new1to1" onclick="showHideData('1to1_relationship','result1to1');">+ New 1:1</button>
        <div id="1to1_relationship" style="display: none;">
            <form method="POST">
                <input type="hidden" name="meetingTemplateData" value="" id="meetingTemplateData" />
                <div class="mb-3 mt-3">
                    <label for="about" class="form-label fw-bolder">Who is your 1:1 with?</label>
                    <select class="form-control" name="emp1to1names" id="emp1to1id" onclick="addNametoInputHidden('#emp1to1id', '#emp1to1value');" required>
                        <option value="" disabled selected hidden>Select a user...</option>
                        <?php foreach ($users as $user) {  ?>
                            <option value="<?php echo esc_attr($user->ID) ?>" data-display-name="<?php echo esc_attr($user->display_name) ?>"><?php echo esc_html($user->display_name) ?></option>
                        <?php  } ?>
                    </select>
                    <input type="hidden" name="emp1to1name" id="emp1to1value" value="">
                    <p class="text-danger"> <?php if (isset($nameerror)) echo $nameerror; ?> </p>
                </div>
                <div class="mb-3 mt-3">
                    <h3>Meeting Time</h3>
                    <label for="meeting" class="form-label fw-bold">Frequency</label>
                    <select class="form-control" name="meetingFrequency" required>
                        <!-- <option value="Weekly" selected>Weekly</option>
                        <option value="Bi-Weekly">Bi-Weekly</option>
                        <option value="Monthly">Monthly</option> -->
                        <option value="Once" selected>Once (not recurring)</option>
                    </select>
                    <p class="text-danger"> <?php if (isset($meetingFrequencyError)) echo $meetingFrequencyError; ?> </p>
                    <div class="d-flex mt-3 gap-4">
                        <div>
                            <label for="meeting" class="form-label fw-bold">Next Meeting</label>
                            <input type="date" class="form-control cursor" name="meetingDate" id="meetingDate" max="2030-12-31" onclick="futureDate()">
                        </div>
                        <div>
                            <label for="meeting" class="form-label fw-bold">Time</label>
                            <input type="time" class="form-control cursor" name="meetingTime" value="13:30">
                        </div>
                    </div>
                    <p class="text-danger" id="dateError"> <?php if (isset($meetingTimeError)) echo $meetingTimeError;
                                                            if (isset($meetingDateError)) echo $meetingDateError;
                                                            if (isset($validDate)) echo $validDate; ?> </p>
                    <p class="mt-3 form-text"> Your 1:1s will be weekly </p>
                </div>
                <div class="mb-3 mt-3">
                    <h3>Default 1:1 template</h3>
                    <p>The selected template will be applied to all upcoming 1:1s with this person.</p>
                    <p class="btn browse-templates" value="Browse templates" id="browseTemplate" data-bs-toggle="modal" data-bs-target="#templateModal">Browse templates
                    <p>
                    <p class="text-danger text-center"> <?php if (isset($templateError)) echo $templateError; ?> </p>
                </div>
                <!-- <div class="mb-3 mt-3">
                <h3>Talking points from previous meeting</h3>
                <p class="form-text"Ensure that you never miss an important conversation. Unchecked talking points from the previous meeting will show up in the next meeting.</p>
                </div> -->
                <div class="mb-3 mt-3">
                    <input class='tgl tgl-light' id='status' type='checkbox' name="status" value="ON">
                    <label class='tgl-btn' for='status'></label>
                    <span>1:1s ON</span>
                    <input type="submit" class="button button-primary float-end share" name="submit_1to1" value="Save">
                </div>
            </form>
        </div>
        <div id="result1to1" style="display: block;">
            <?php foreach ($results as $result) {
                if ($result->meeting_status === 'off') {
                    $rowclass = ' alert alert-secondary';
                    $colclass = ' mb-0';
                } else {
                    $rowclass = '';
                    $colclass = ' mb-3';
                } ?>
                <div class="row mt-5 mb-5 one_to_one_usersList<?php if (isset($rowclass)) echo $rowclass; ?>">
                    <div class="col-4<?php if (isset($colclass)) echo $colclass; ?>">
                        <span class="card-usernames one_to_oneby"><?php echo initials($result->sent_by_username); ?></span>
                        <?php echo (capitalizeWords($result->sent_by_username)); ?>
                    </div>
                    <div class="col-4 text-end<?php if (isset($colclass)) echo $colclass; ?>"><?php echo dateFormat($result->date_time); ?>
                    </div>
                    <div class="col-4 text-end<?php if (isset($colclass)) echo $colclass; ?>">
                        <span class="cursor" data-bs-toggle="modal" data-bs-target="#recieved1_to_1" onclick="getData(<?php echo $result->template_id; ?>);">&#65125;</span>
                    </div>
                </div>
            <?php } ?>
            <!-- Pagination links -->
            <div class="pagination float-end">
                <span class="displaying-num"><?php echo !empty($totalRecords) ? $totalRecords . ' item' . ($totalRecords > 1 ? 's' : '') : '0 item'; ?></span>&nbsp;
                <?php
                if ($totalPages > 1) {
                    $currentPage = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

                    if ($currentPage === 1) {
                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span> &nbsp;';
                    }

                    // Display previous page button ('<')
                    if ($currentPage > 1) {
                        echo '<a class="ms-2" href="' . esc_url(add_query_arg('paged', ($currentPage - 1))) . '">
                        <span class="tablenav-pages-navspan button" aria-hidden="true">‹</span>
                        </a> &nbsp;';
                    }

                    // Display current page number and total pages
                    echo '<span class="tablenav-paging-text">' . $currentPage . ' of <span class="total-pages">' . $totalPages . '</span></span>';

                    // Display next page button ('>')
                    if ($currentPage < $totalPages) {
                        echo '<a class="ms-2" href="' . esc_url(add_query_arg('paged', ($currentPage + 1))) . '">
                        <span class="tablenav-pages-navspan button" aria-hidden="true">›</span>
                        </a>';
                    }
                    if ($currentPage === $totalPages) {
                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
                    }
                }
                ?>

            </div>
        </div>

        <!-- create template Modal -->
        <div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" name="createTemplate" onsubmit="return templateForm(event)">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="templateModalLabel">Create 1:1 template</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 mt-3">
                                <label for="templateTopic" class="form-label fw-bold">Name</label>
                                <input type="text" class="form-control" name="topicName" placeholder='e.g. "Weekly check-in 1:1"' required>
                                <p class="text-danger templateError d-none">Name must be filled out</p>
                            </div>
                            <div class="mb-3 mt-3">
                                <label for="templateAgenda" class="form-label fw-bold">Agenda</label>
                                <div class="form-text cursor agendaBtn">+Add talking point</div>
                                <p class="text-danger templateError d-none">Agenda must be filled out</p>
                            </div>
                            <div class="mb-3 mt-3">
                                <label for="templateActionItems" class="form-label fw-bold">Action items</label>
                                <span class="form-text">(optional)</span>
                                <div class="form-text cursor" onclick="addInput(this,'actionItem');">+Add action item</div>
                                <p class="text-danger templateError d-none"></p>
                            </div>
                            <div class="mb-3 mt-3">
                                <label for="about" class="form-label fw-bolder">Category</label>
                                <span class="form-text">(optional)</span>
                                <select class="form-control" id="templateCategory1to1" name="category">
                                    <option value="No Category" disabled selected hidden>No Category</option>
                                    <option value="Tech">Tech</option>
                                    <option value="Biz Tech">Biz Tech</option>
                                    <option value="HR">HR</option>
                                </select>
                            </div>
                            <div class="mb-3 mt-3">
                                <label for="about" class="form-label fw-bolder">Description</label>
                                <span class="form-text">(optional)</span>
                                <textarea class="form-control" id="templateDescription" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button me-2 " data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="button button-primary" name="meetingTemplate" value="Save changes">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- fetched data in modal -->
        <div class="modal fade" id="recieved1_to_1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" id="newTemplateData">
                    <!-- <input type="hidden" value="" id="saveTemplateData" /> -->
                    <div class="modal-content">
                        <div class="modal-body" id="recieved1_to_1_body"></div>
                        <div class="modal-footer">
                            <button type="button" class="button me-2 close-template" data-bs-dismiss="modal">Close</button>
                            <input type="submit" class="button button-primary" name="saveTemplateData" value="Save your changes">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
    ob_end_flush();
}
    ?>