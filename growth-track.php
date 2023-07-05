<?php
// Callback function for growth track page
function growth_track_callback()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback_growth_track';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {

        echo "<div class='alert alert-danger viewFeedback'>Table does not found, so data cannot be retrieved!</div>";
    } else {
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name  Group BY `positions_name`,`levels_name`"));
        

        if (empty($rows)) {
            echo '<div class="danger">No Data Found :( </div>';
            return;
        }

        else {
            $grouped_rows = array();
        foreach ($rows as $row) {
            $position_name = $row->positions_name;
            $level_name = $row->levels_name;
            $functions_name = $row->functions_name;
            $specifications = $row->specifications;
        
            if (!isset($grouped_rows[$position_name])) {
                $grouped_rows[$position_name] = array(
                    'levels' => array(),
                    'functions' => array(),
                    'specifications' => array(),
                );
            }
        
            $grouped_rows[$position_name]['levels'][] = $level_name;
            $grouped_rows[$position_name]['functions'][$position_name]= $functions_name;
            $grouped_rows[$position_name]['specifications'][$level_name]= $specifications;
        }
        }
    }


?>
    <div class="feedback_container">
        <div class="row mt-3">
            <ul class="col-2">
                <h6 class="mt-4">GROWTH TRACKS</h6>
                <?php foreach ($grouped_rows as $key => $position_name) { ?>
                    <li class="btn role-btns" onclick="showTable('table_data_<?php echo $key ?>')"><?php echo capitalizeWords($key) ?></li>
                <?php } ?>
            </ul>
            <div class="col-10" id="tableData">
                <div class="scroll-table">
                    <?php foreach($grouped_rows as $key => $data){ ?>
                    <table class="table table-borderless nowrap d-none" id="table_data_<?php echo $key ?>">
                        <thead>
                            <tr>
                                <td class="text-center"></td>
                                
                                    <?php foreach($data['levels'] as $key => $levelName){ ?>
                                    <td class="text-center" id=""><?php  echo (capitalizeWords($levelName)); ?></td>
                                <?php  }?>
                               
                            </tr>
                            
                            <th>Competencies</th>
                            <?php
                            foreach($data['levels'] as $key => $levelName){ 
                                $words = explode(" ", $levelName); ?>

                                <th value="<?php echo $levelName; ?>"><?php foreach($words as $word) { echo strtoupper(substr($word, 0, 1)); } ?></th>
                            <?php } ?>
                        </thead>
                        
                        <tbody>
                            <?php 
                                echo "<tr id='position_name_".$key."'>";
                                foreach($data['functions'] as $key => $functionsName){
                                echo "<td>" . $functionsName . "</td>";
                            }
                            foreach($data['specifications'] as $key => $specifications){
                                echo "<td>" . $specifications . "</td>";
                            }
                                echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>