<?php
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    if ((isset($_POST['post_id']) && (isset($_POST['date'])))) {
        global $wpdb;
        
        $wpdb->update( $wpdb->posts, array( 'post_date' => $_POST['date'],  ), 
            array( 'ID' => $_POST['post_id'] ), array( '%s' ), array( '%d' ) );      
        die('updated');  
    }
}

global $edit_flow;

if($_GET['edit_flow_custom_status_filter']) {
    $edit_flow->options['custom_status_filter'] = $_GET['edit_flow_custom_status_filter'];  
    update_option($edit_flow->get_plugin_option_fullname('custom_status_filter'), 
        $_GET['edit_flow_custom_status_filter']);
}

#echo '<pre>';
#print_r($edit_flow->options);
#echo '</pre>'; 

date_default_timezone_set('UTC');
$dates = array();
if ($_GET['date']) {
    $time = strtotime( $_GET['date'] );
    $date = date('Y-m-d', $time);
} else {
    $date = date('Y-m-d');
}

for ($i=0; $i<7; $i++) {
    $dates[$i] = $date;
    $date = date('Y-m-d', strtotime("-1 day", strtotime($date)));
}

?>
	<style>
		.week-heading {
	        background: #6D6D6D url('<?php echo(path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/../../../wp-admin/images/menu-bits.gif")); ?>') repeat-x scroll left top;
	    }
	</style>
    <div id="main-content"><!-- Main Content -->
    <div style="float: right">
    <form method="GET" action="">
    <?php
    if ($_GET['date']) { echo '<input type="hidden" name="date" value="'. $_GET['date'] . '"/>'; }
    ?>
    <select name="<?php  echo $edit_flow->get_plugin_option_fullname('custom_status_filter') ?>" id="custom_status_filter">
    <option value="all" <?php if ($edit_flow->get_plugin_option('custom_status_filter')=='all') { echo 'selected="selected"';}?>>All Posts</option>
    <option value="my-posts" <?php if ($edit_flow->get_plugin_option('custom_status_filter')=='my-posts') { echo 'selected="selected"';}?>>My Posts</option>
    <?php $statuses = $edit_flow->custom_status->get_custom_statuses() ?>
        <?php foreach($statuses as $status) : ?>

                <?php $selected = ($edit_flow->get_plugin_option('custom_status_filter')==$status->slug) ? 'selected="selected"' : ''; ?>
                <option value="<?php esc_attr_e($status->slug) ?>" <?php echo $selected ?>>
                        <?php esc_html_e($status->name); ?>
                </option>

        <?php endforeach; ?>
    </select>
    <input type="hidden" name="page" value="edit-flow/calendar"/>
    <input type="submit" value="filter"/>
    </form>
	</div>
    	<div id="calendar-title"><!-- Calendar Title -->
    		<div class="icon32" id="icon-edit"><br/></div><!-- These two lines will now fit with the WP style. The icon-edit ID could be changed if we'd like a different icon to appear there. -->
    		<h2><?php echo date('F d, Y', strtotime($dates[count($dates)-1])) ?> - 
        	<?php echo date('F d, Y', strtotime($dates[0])) ?></h2>
    	</div><!-- /Calendar Title -->

    	<div id="calendar-wrap"><!-- Calendar Wrapper -->
    		<ul class="day-navigation">
    			<li class="previous-week">
    			    <a href="<?php echo ef_get_calendar_previous_link($dates[count($dates)-1]) ?>">
    		            Previous 7 days
    		        </a>
    		    </li>
    			<li class="next-week">
        			<a href="<?php echo ef_get_calendar_next_link($dates[0]) ?>">
        		        Next 7 days
        		    </a>
    			</li>
    		</ul>

    		<div id="week-wrap"><!-- Week Wrapper -->
    			<div class="week-heading"><!-- New HTML begins with this week-heading div. Adds a WP-style dark grey heading to the calendar. Styles were added inline here to save having 7 different divs for this. -->
    				<div class="day-heading first-heading" style="width: 13.8%; height: 100%; position: absolute; left: 0%; top: 0%; ">
    					<?= date('F d', strtotime($dates[6])) ?>
    				</div>
    				<div class="day-heading" style="width: 13.8%; height: 100%; position: absolute; left: 15.6%; top: 0%; ">
					<?= date('F d', strtotime($dates[5])) ?>
    				</div>
    				<div class="day-heading" style="width: 13.8%; height: 100%; position: absolute; left: 30%; top: 0%; ">
					<?= date('F d', strtotime($dates[4])) ?>
    				</div>
    				<div class="day-heading" style="width: 13.8%; height: 100%; position: absolute; left: 44.1%; top: 0%; ">
					<?= date('F d', strtotime($dates[3])) ?>
    				</div>
    				<div class="day-heading" style="width: 13.8%; height: 100%; position: absolute; left: 58.4%; top: 0%; ">
					<?= date('F d', strtotime($dates[2])) ?>
    				</div>
    				<div class="day-heading" style="width: 13.8%; height: 100%; position: absolute; left: 72.2%; top: 0%; ">
					<?= date('F d', strtotime($dates[1])) ?>
    				</div>
    				<div class="day-heading last-heading" style="width: 13.8%; height: 100%; position: absolute; left: 87%; top: 0%; ">
					<?= date('F d', strtotime($dates[0])) ?>
    				</div>
    			</div><!-- From here on it is the same HTML but you can add two more week-units now to get the 7 days into the calendar. -->
    			
    			<?php
            	foreach (array_reverse($dates) as $date) {
            	    $cal_posts = ef_get_calendar_posts($date);
            	?>
    			<div class="week-unit"><!-- Week Unit 1 -->
    				<ul id="<?= date('Y-m-d', strtotime($date)) ?>" class="week-list connectedSortable">
    				    <?php
    				    foreach ($cal_posts as $cal_post) {
            		        $cats = wp_get_object_terms($cal_post->ID, 'category');
            		        $cat = $cats[0]->name;
            		        if (count($cats) > 1) { 
            		            $cat .= " and  " . (count($cats) - 1);
            		            if (count($cats)-1 == 1) { $cat .= " other"; }
            		            else { $cat .= " others"; }
            		        }
            		        
            		    ?>
    					<li id="<?= $cal_post->ID ?>">
    						<span class="item-handle"><img src="<?= EDIT_FLOW_URL_FROM_ROOT ?>img/drag_handle.jpg" alt="Drag Handle" /></span>
    						<h5 class="item-headline">
    						    <?php echo edit_post_link($cal_post->post_title, '', '', $cal_post->ID); ?>
    						</h5>
    						<ul class="item-metadata">
    							<li class="item-author">By <?php echo $cal_post->display_name ?></li>
    							<li class="item-category">
    							    <?= $cat ?>
    							</li>
    							<div style="clear:both"></div>
    						</ul>
    					</li>
    					<?php
            	        }
            	        ?>
    				</ul>
    			</div><!-- /Week Unit 1 -->
                <?php
    	        }
    	        ?>
    	        
    			<div style="clear:both"></div>
    		</div><!-- /Week Wrapper -->
    		<ul class="day-navigation">
    			<li class="previous-week">
    			    <a href="<?php echo ef_get_calendar_previous_link($dates[count($dates)-1]) ?>">
    		            Previous 7 days
    		        </a>
    			</li>
    			<li class="next-week">
        			<a href="<?php echo ef_get_calendar_next_link($dates[0]) ?>">
        		        Next 7 days
        		    </a>
    			</li>
    		</ul>
    		<div style="clear:both"></div>
    	</div><!-- /Calendar Wrapper -->

    </div><!-- /Main Content -->

<?php 
function ef_get_calendar_previous_link( $date ) {
    $p_date = date('d-m-Y', strtotime("-1 day", strtotime($date)));
	return EDIT_FLOW_CALENDAR_PAGE.'&amp;date='.$p_date;
}

function ef_get_calendar_next_link( $date ) {
    $n_date = date('d-m-Y', strtotime("+7 days", strtotime($date)));
	return EDIT_FLOW_CALENDAR_PAGE.'&amp;date='.$n_date;
}

function ef_get_calendar_posts( $date ) {
 
    global $wpdb, $edit_flow;
    $q_date = date('Y-m-d', strtotime($date));
    
    $sql = "SELECT w.ID, w.guid, w.post_date, u.display_name, w.post_title ";
    $sql .= "FROM " . $wpdb->posts . " w, ". $wpdb->users . " u ";
    $sql .= "WHERE u.ID=w.post_author and ";
    if (($edit_flow->get_plugin_option('custom_status_filter') != 'all') && 
        ($edit_flow->get_plugin_option('custom_status_filter') != 'my-posts')) {
        $sql .= "w.post_status = '" . $edit_flow->get_plugin_option('custom_status_filter') . "' and ";
    }
    if ($edit_flow->get_plugin_option('custom_status_filter') == 'my-posts') {
        $sql .= " u.ID = " . wp_get_current_user()->ID . " and ";
    }
    $sql .= "w.post_type = 'post' and w.post_date like '". $q_date . "%'";
    #echo "<pre>" . $sql . "</pre>";
    $cal_posts = $wpdb->get_results($sql);
    return $cal_posts;
}

?>