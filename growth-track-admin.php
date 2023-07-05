<?php
function growth_track_admin_callback()
{
    ob_start(); // Start output buffering  
    // details form
    if (isset($_POST['addDetails']) && $_POST['addDetails'] === 'Submit') {
        $functionName = $_POST['newFunction'] ?? '';
        $levelName = $_POST['newLevel'] ?? '';
        $positionName = $_POST['newPosition'] ?? '';
        $specifications = $_POST['growthTrackEditor'] ?? '';
        $functionNameError = empty($functionName) ? "Please enter function name" : '';
        $levelNameError = empty($levelName) ? "Please enter level name" : '';
        $positionNameError = empty($positionName) ? "Please enter position name" : '';
        $specificationsError = empty($specifications) ? "Please enter specifications" : '';

        if (empty($functionNameError) && empty($levelNameError) && empty($positionNameError) && empty($specificationsError)) {
            // Validate and sanitize user input here
            $functionName = strtolower(sanitize_text_field($_POST['newFunction']));
            $levelName = strtolower(sanitize_text_field($_POST['newLevel']));
            $positionName = strtolower(sanitize_text_field($_POST['newPosition']));
            $specifications = wp_kses_post($_POST['growthTrackEditor']);
            // Insert data into table
            global $wpdb;
            $table_name = $wpdb->prefix . 'feedback_growth_track';

            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name)
                echo "<p class='alert alert-danger'>Oops! Table does not found, so details cannot be inserted!</p>";
            else {

                // Check if a record with the same positions name and levels name already exists in the table
                $existing_record = $wpdb->get_row($wpdb->prepare("SELECT id,specifications FROM $table_name WHERE positions_name = %s AND levels_name = %s AND functions_name = %s", $positionName, $levelName, $functionName));
                if ($existing_record) {
                    $data = array(
                        'specifications' => $specifications
                    );
                    $where = array(
                        'id' => $existing_record->id
                    );
                    $updateResult = $wpdb->update($table_name, $data, $where);
                    if ($updateResult === false) {
                        // Log the SQL error
                        error_log("Error inserting data into database: " . $wpdb->last_error);
                    } else {
                        // Display a success message to the user
                        $UpdateResult =  "<p class='alert alert-success'>Details updated successfully!</p>";
                    }
                }
                else {
                    $data = array(
                        'functions_name' =>  strtolower($functionName),
                        'levels_name' => strtolower($levelName),
                        'positions_name' => strtolower($positionName),
                        'specifications' => $specifications
                    );

                    $result = $wpdb->insert($table_name, $data);

                    if ($result === false) {
                        // Log the SQL error
                        error_log("Error inserting feedback into database: " . $wpdb->last_error);
                    } else {
                        // Display a success message to the user
                        $insertResult =  "<p class='alert alert-success'>Details inserted successfully!</p>";
                    }
                }
            }
        }
    }

?>
    <div class="feedback_container">
        <div class="row mt-3">
            <div class="col-8" style="margin:auto;">
                <form method="POST">
                    <div>
                        <label for="inputFunctions" class="form-label">Add New Function</label>
                        <input type="text" class="form-control mb-2" id="newFunction" aria-describedby="addFunctions" name="newFunction" required>
                        <?php if (isset($functionNameError) && !empty($functionNameError)) : ?>
                        <p class="alert alert-danger"><?php echo $functionNameError; ?></p>
                        <?php endif; ?>
                        <label for="inputPosition" class="form-label">Add New Position</label>
                        <input type="text" class="form-control mb-2" id="newPosition" aria-describedby="addPosition" name="newPosition" required>
                        <?php if (isset($positionNameError) && !empty($positionNameError)) : ?>
                        <p class="alert alert-danger"><?php echo $positionNameError; ?></p>
                        <?php endif; ?>
                        <label for="inputLevel" class="form-label">Add New Level</label>
                        <input type="text" class="form-control mb-2" id="newLevel" aria-describedby="addLevel" name="newLevel" required>
                        <?php if (isset($levelNameError)&& !empty($levelNameError)) : ?>
                        <p class="alert alert-danger"><?php echo $levelNameError; ?></p>
                        <?php endif; ?>
                        <label for="inputDetails" class="form-label">Add Specifications</label>
                        <?php
                        $content = '';
                        $editor_id = 'growthTrackEditor';
                        $settings = array( 'media_buttons' => false,
                        'textarea_name' => $editor_id,
                        'textarea_rows' => 10,
                        'teeny' => false,
                        'tinymce' => true );
                        wp_editor( $content, $editor_id, $settings);
                            ?>
                        <?php if (isset($specificationsError) && !empty($specificationsError)) : ?>
                        <div class="alert alert-danger"><?php echo $specificationsError; ?></div>
                        <?php endif; ?>
                    </div>
                    <p class="text-danger"> <?php if (isset($insertResult) && !empty($insertResult)) echo $insertResult;
                    if (isset($UpdateResult) && !empty($UpdateResult)) echo $UpdateResult; ?> </p>
                    <button type="submit" class="button button-primary float-end" name="addDetails" value="Submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

<?php
    ob_end_flush();
}
?>