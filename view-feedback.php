<?php
// Callback function for view feedback menu page
function view_feedback_callback()
{
    echo '<div class="feedback_container"><h3>View Feedback</h3>';
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';
    $current_user = wp_get_current_user();
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name)
        echo "<div class='alert alert-danger viewFeedback'>Table does not found, so feedbacks cannot be retrieved!</div>";

    else {
        $resultsPerPage = 10;
        if ($current_user->roles[0] === 'employee') {
            // Count total number of records
            $totalRecords = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE sent_to_userid = '{$current_user->ID}' OR sent_by_userid = '{$current_user->ID}'");
    
            // Calculate the total number of pages
            $totalPages = ceil($totalRecords / $resultsPerPage);
    
            // Get the current page number
            $pageNumber = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
            $startIndex = max(0, ($pageNumber - 1) * $resultsPerPage);

            $results = $wpdb->get_results("SELECT * FROM $table_name WHERE sent_to_userid = '{$current_user->ID}' OR sent_by_userid = '{$current_user->ID}' ORDER BY feedback_send_date DESC LIMIT $startIndex, $resultsPerPage");
        } 
        else if ($current_user->roles[0] === 'administrator') {
            $totalRecords = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            // Calculate the total number of pages
            $totalPages = ceil($totalRecords / $resultsPerPage);
    
            // Get the current page number
            $pageNumber = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
            $startIndex = max(0, ($pageNumber - 1) * $resultsPerPage);

            $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY feedback_send_date DESC LIMIT $startIndex, $resultsPerPage");
        } 
        else {
            // User doesn't have required role
            echo "<div class='alert alert-danger viewFeedback'>You don't have the required role to view feedbacks!</div>";
            return;
        }

        if (empty($results)) echo '<div class="danger emptyfeedback viewFeedback">No Feedback Found :( </div>';

        else { // Display feedback results 
?>
            <div class="row viewFeedback">
                <?php foreach ($results as $user) { ?>
                    <div class="col-8 feedback-received-cards">
                        <div class="card mb-3">
                            <div>
                                <span><span class="card-usernames by"><?php echo initials($user->sent_by_username); ?></span>
                                    <?php echo (capitalizeWords($user->sent_by_username)); ?>
                                </span>
                                <span class="float-end"><span class="card-usernames to"><?php echo initials($user->sent_to_username);  ?></span>
                                    <?php echo (capitalizeWords($user->sent_to_username)); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo ($user->feedback_message); ?></p>
                                <div class="mb-2 mt-2">
                                    <label class="badge rounded-pill PillList-item d-flex flex-wrap">

                                        <?php
                                        $suggestedmsgs = explode('|', $user->suggested_message);
                                        foreach ($suggestedmsgs as $suggestedmsg) { ?>

                                            <span class="suggestedmsg"><?php echo $suggestedmsg; ?></span>

                                        <?php } ?>
                                    </label>
                                </div>
                                <?php if($user->sent_by_userid == $current_user->ID) 
                                $dateMsg = 'Feedback Sent On: ';
                                else $dateMsg = 'Feedback Recieved On: ';  ?>
                                <p class="card-text text-end"><?php echo $dateMsg. date_format(new DateTime($user->feedback_send_date), "d/m/Y"); ?></p>
                            </div>
                        </div>
                    </div>
                <?php
                }  ?>

            <!-- Pagination links -->
            <div class="pagination justify-content-end col-8">
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
            </div>
<?php }
    }
}

?>