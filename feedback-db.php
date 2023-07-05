<?php
// Define a function to create the feedback table
function create_feedback_table() {
    global $wpdb;
    $feedbackTable = $wpdb->prefix . 'feedback';
    if ($wpdb->get_var("show tables like '$feedbackTable'") != $feedbackTable) {
        $sql  = "CREATE TABLE `$feedbackTable` (";
        $sql .= " `id` int(11) NOT NULL auto_increment, ";
        $sql .= " `sent_to_userid` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `sent_to_username` varchar(255) NOT NULL, ";
        $sql .= " `feedback_message` text NOT NULL,";
        $sql .= " `suggested_message` varchar(500) NOT NULL, ";
        $sql .= " `sent_by_userid` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `sent_by_username` varchar(255) NOT NULL, ";
        $sql .= " `feedback_send_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " PRIMARY KEY `id` (`id`), ";
        $sql .= " FOREIGN KEY (`sent_to_userid`) REFERENCES wp_users(`ID`), ";
        $sql .= " FOREIGN KEY (`sent_by_userid`) REFERENCES wp_users(`ID`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function create_growthTrack_table() {
    global $wpdb;
    $growthtrackTable = $wpdb->prefix . 'feedback_growth_track';
    if ($wpdb->get_var("show tables like '$growthtrackTable'") != $growthtrackTable) {
        $sql  = "CREATE TABLE `$growthtrackTable` (";
        $sql .= " `Id` int(11) NOT NULL auto_increment, ";
        $sql .= " `functions_name` varchar(255) NOT NULL, ";
        $sql .= " `positions_name` varchar(255) NOT NULL, ";
        $sql .= " `levels_name` varchar(255) NOT NULL, ";
        $sql .= " `specifications` text NOT NULL, ";
        $sql .= " `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " PRIMARY KEY (`Id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function create_1to1meeting_table() {
    global $wpdb;
    $emp1to1MeetingTable = $wpdb->prefix . 'feedback_1_to_1_meeting';
    if ($wpdb->get_var("show tables like '$emp1to1MeetingTable'") != $emp1to1MeetingTable) {
        $sql  = "CREATE TABLE `$emp1to1MeetingTable` (";
        $sql .= " `Id` int(11) NOT NULL auto_increment, ";
        $sql .= " `template_id` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `sent_to_userid` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `sent_to_username` varchar(255) NOT NULL, ";
        $sql .= " `sent_by_userid` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `sent_by_username` varchar(255) NOT NULL, ";
        $sql .= " `frequency` varchar(255) NOT NULL, ";
        $sql .= " `date_time` DATETIME, ";
        $sql .= " `meeting_status` ENUM('on', 'off') DEFAULT 'off', ";
        $sql .= " `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " PRIMARY KEY (`Id`),";
        $sql .= " FOREIGN KEY (`template_id`) REFERENCES wp_feedback_1_to_1_meeting_template(`template_id`)";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function create_1to1meeting_template_table() {
    global $wpdb;
    $emp1to1MeetingTemplateTable = $wpdb->prefix . 'feedback_1_to_1_meeting_template';
    if ($wpdb->get_var("show tables like '$emp1to1MeetingTemplateTable'") != $emp1to1MeetingTemplateTable) {
        $sql  = "CREATE TABLE `$emp1to1MeetingTemplateTable` (";
        $sql .= " `template_id` int(11) NOT NULL auto_increment, ";
        $sql .= " `template_name` varchar(255) NOT NULL, ";
        $sql .= " `template_category` varchar(255) NOT NULL, ";
        $sql .= " `template_description` text NOT NULL,";
        $sql .= " `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " PRIMARY KEY (`template_id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function create_1to1meeting_agenda_table() {
    global $wpdb;
    $emp1to1MeetingAgendaTable = $wpdb->prefix . 'feedback_1_to_1_meeting_agenda';
    if ($wpdb->get_var("show tables like '$emp1to1MeetingAgendaTable'") != $emp1to1MeetingAgendaTable) {
        $sql  = "CREATE TABLE `$emp1to1MeetingAgendaTable` (";
        $sql .= " `agenda_id` int(11) NOT NULL auto_increment, ";
        $sql .= " `template_id` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `talking_point` text NOT NULL,";
        $sql .= " `agenda_status` ENUM('on', 'off') DEFAULT 'on', ";
        $sql .= " `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " PRIMARY KEY (`agenda_id`),";
        $sql .= " FOREIGN KEY (`template_id`) REFERENCES wp_feedback_1_to_1_meeting_template(`template_id`)";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql);
    }
}

function create_1to1meeting_actionItem_table() {
    global $wpdb;
    $emp1to1MeetingActionItemTable = $wpdb->prefix . 'feedback_1_to_1_meeting_actionitem';
    if ($wpdb->get_var("show tables like '$emp1to1MeetingActionItemTable'") != $emp1to1MeetingActionItemTable) {
        $sql  = "CREATE TABLE `$emp1to1MeetingActionItemTable` (";
        $sql .= " `action_id` int(11) NOT NULL auto_increment, ";
        $sql .= " `template_id` bigint(20) UNSIGNED NOT NULL, ";
        $sql .= " `action_item` text NOT NULL,";
        $sql .= " `action_status` ENUM('on', 'off') DEFAULT 'on', ";
        $sql .= " `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " PRIMARY KEY (`action_id`),";
        $sql .= " FOREIGN KEY (`template_id`) REFERENCES wp_feedback_1_to_1_meeting_template(`template_id`)";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

?>