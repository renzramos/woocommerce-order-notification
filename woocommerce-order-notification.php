<?php
/*
Plugin Name: Woocommerce Order Notification
Plugin URI: http://www.renzramos.com
Description: Simple Woocommerce order notification
Version: 1.0
Author: Renz Ramos
Author URI: http://www.renzramos.com
License: GPL2
*/


DEFINE('WON_TITLE', 'Order Notification');
DEFINE('WON_SLUG', 'woocommerce-order-notification');

/**
 * Register a custom menu page.
 */
function wpdocs_register_my_custom_menu_page(){
    add_menu_page( WON_TITLE, WON_TITLE, 'manage_options', WON_SLUG, 'woocommerce_order_notification_callback', 'dashicons-welcome-comments' ,6 ); 
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );
 
/**
 * Display a custom menu page
 */
function woocommerce_order_notification_callback(){
    
    if (isset($_GET['action'])){
        
        if ($_GET['action'] == 'save'){
            update_option('won_data', $_GET); 
            wp_redirect(home_url() . '/wp-admin/admin.php?page=woocommerce-order-notification');
        }  
    }
    
    $won_data = get_option('won_data');
    $won_template = (isset($won_data['won']['template'])) ? $won_data['won']['template'] : '';
    $random_orders = (isset($won_data['won']['random_orders'])) ? 'checked' : '';
    
    
    ?>
    <div class="wrap">
        <h1>Woocommerce <?php echo WON_TITLE; ?> <small>1.0</small></h1>
        <small>Developed by Renz Ramos</small>
        
        <div class="data-container">
            <h2>Data</h2>
            <form id="won-form">
                <input type="hidden" name="page" value="woocommerce-order-notification"/>
                <div class="form-group">
                    <label>Template</label>
                    <textarea name="won[template]" rows="1"><?php echo $won_template; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Include Random Orders?</label>
                    <input type="checkbox" name="won[random_orders]" <?php echo $random_orders; ?> />
                </div>
                
                <hr>
                <button class="button-primary" name="action" value="save">Save</button>
            </form>
        </div>
        
        <div class="preview-container">
            <h2>Data Preview ( Completed Orders ) </h2>
            
            
            <?php
            
            $args = array(
                'post_type' => 'shop_order',
        	    'post_status' => 'wc-order-completed',
            	'posts_per_page' => '-1',
            );
            $query = new WP_Query( $args );
            
            if ( $query->have_posts() ) {
                ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th id="column-order" class="text-center manage-column column-thumbnail" scope="col">Order</th>
                            <th id="column-content" class="manage-column column-content num" scope="col">Content</th> 
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                	while ( $query->have_posts() ) {
                	    
                		$query->the_post();
                		
                		
                		$order_id = get_the_id();
                		$order = wc_get_order( $order_id );
                		$order_data = $order->get_data(); 
                		
                		$items = $order->get_items(); 
                		
                		$date_created = $order_data['date_created'];
                		$name = $order_data['billing']['first_name'];
                		$email = $order_data['billing']['email'];
                		$state = $order_data['billing']['state'];
                		$country = $order_data['billing']['country'];
                		
                		
                		$wc_countries = new WC_Countries();
                		$states = $wc_countries->get_states($country);
                		
                		$state = $states[$state];
                		
                		
                		$content = $won_template;
                		
                		$content = str_replace('[state]',$state, $content);
                		
                		?>
                		<tr class="alternate">
                            <td class="column-order text-center" ><?php echo $order_id; ?></td>
                            <td class="column-content">
                                <?php //echo $content; ?>
                                <?php 
                                foreach ($items as $item){ 
                                    $product_id = $item->get_product_id();
                                    $product_name = get_the_title($product_id);
                                    $product_link = get_permalink($product_id);
                                    
                                    $product_content = str_replace('[product_name]', '<a href="' . $product_link . '">' .  $product_name . '</a>', $content);
                                    $ago = time_elapsed_string($date_created);
                                    $thumbnail_url = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
                                   
                                ?>
                                
                                <div class="recent-product-order">
                                    
                                    <div class="image">
                                        
                                        <img src="<?php echo $thumbnail_url; ?>"/>
                                        
                                    </div>
                                    
                                    <div class="content">
                                        <p><?php  echo $product_content; ?></p>
                                        <p>
                                            <?php
                                             echo $ago;
                                            ?>
                                        </p>
                                    </div>
                                    <div class="clearfix"></div>
                                    
                                </div>
                                
                                <?php } ?>
                            </td>
                        </tr>
                		<?php
                		
                		
                	}
                	wp_reset_postdata();
                	?>
                	
                    </tbody>
                </table>
            	    <?php
            } else {
            	echo 'No available orders.';
            }
            ?>
            
            
        </div>
    </div>
    <?php
}


function won_frontend_notification() {
    
    $won_data = get_option('won_data');
    $won_template = (isset($won_data['won']['template'])) ? $won_data['won']['template'] : '';
    $random_orders = (isset($won_data['won']['random_orders'])) ? 'checked' : '';
    
    
    $args = array(
        'post_type' => 'shop_order',
	    'post_status' => 'wc-order-completed',
    	'posts_per_page' => '-1',
    );
    $query = new WP_Query( $args );
    
    $notification_data = array();
    
    
    
	
    $wc_countries = new WC_Countries();		
    		
    if ( $query->have_posts() ) {
        
        while ( $query->have_posts() ) {
                	    
    		$query->the_post();
    		
    		
    		$order_id = get_the_id();
    		$order = wc_get_order( $order_id );
    		$order_data = $order->get_data(); 
    		
    		$items = $order->get_items(); 
    		
    		$date_created = $order_data['date_created'];
    		$name = $order_data['billing']['first_name'];
    		$email = $order_data['billing']['email'];
    		$state = $order_data['billing']['state'];
    		$country = $order_data['billing']['country'];
    		
    		
        	$states = $wc_countries->get_states($country);
    		$state = $states[$state];
    		
    		
    		$content = $won_template;
    		
    		$content = str_replace('[state]',$state, $content);
            
            foreach ($items as $item){ 
                
                $product_id = $item->get_product_id();
                $product_name = get_the_title($product_id);
                $product_link = get_permalink($product_id);
                
                $product_content = str_replace('[product_name]', '<br><a href="' . $product_link . '">' .  $product_name . '</a>', $content);
                $ago = time_elapsed_string($date_created);
                $thumbnail_url = get_the_post_thumbnail_url( $product_id);
                
                
                $notification_data[] = array(
                    'link' =>  $product_link,  
                    'product_content' =>  $product_content,  
                    'thumbnail_url' =>  $thumbnail_url,  
                    'ago' =>  $ago,  
                );
                                   
            }
                		
        }
        
    }
    
    // random orders
    $country = 'US';
    $random_orders = (isset($won_data['won']['random_orders'])) ? 'checked' : '';
    if ($random_orders == 'checked'){
        
        $args = array( 
            'orderby' => 'rand',
            'posts_per_page' => '5', 
            'post_type' => 'product'
        );
        $query = new WP_Query( $args );
        
        while ( $query->have_posts() ) : $query->the_post();
            
            $states = $wc_countries->get_states($country);
            $state = $states[array_rand($states)];
            
            $content = $won_template;
            $content = str_replace('[state]', $state, $content);
            
            
            $product_id = get_the_ID();
            $product_name = get_the_title($product_id);
            $product_link = get_permalink($product_id);
            
            $product_content = str_replace('[product_name]', '<br><a href="' . $product_link . '">' .  $product_name . '</a>', $content);
            $ago = time_elapsed_string($date_created);
            $thumbnail_url = get_the_post_thumbnail_url( $product_id);
            
            
             
            $ago_after = array('second','minute');
            $ago_after = $ago_after[array_rand($ago_after)];    
            
            $ago_count = 0;
            if ($ago_after == 'second'){
                $ago_count = rand(1,60);
            }else{
                $ago_count = rand(1,30);
            }
            
            
            $ago_after = ($ago_count > 1) ? $ago_after . 's' : $ago_after;
            
            
            $ago = $ago_count . ' ' . $ago_after  . ' ago';
            
            $data = array(
                'link' =>  $product_link,  
                'product_content' =>  $product_content,  
                'thumbnail_url' =>  $thumbnail_url,  
                'ago' =>  $ago,  
            );
            $notification_data[] = $data;
            
        endwhile;
        
    }
    
    shuffle($notification_data);
    ?>
    
    <script>
        var notificationData = jQuery.parseJSON('<?php echo $notification_data; ?>');
        console.log(notificationData);
    </script>
    <div class="won-container">
        <div class="notification-container">
        	
        	<?php foreach ($notification_data as $key => $data){ ?>
        	<div class="notification notification-<?php echo $key; ?>">
        		<div class="image">
        		    <a href="<?php echo $data['link']; ?>">
        			    <img src="<?php echo $data['thumbnail_url']; ?>"/>
        			</a>
        		</div>
        		<div class="text">
        			<header><?php echo $data['product_content']; ?></header>
        			<p class="ago"><?php echo $data['ago']; ?></p>
        		</div>
        		<div style="clear:both"></div>
        	</div>
        	<?php } ?>
        </div>
        
    </div>
    <?php
}
add_action( 'wp_footer', 'won_frontend_notification', 100 );



function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}


function won_enqueue_scripts($hook) {
        if($hook != 'toplevel_page_woocommerce-order-notification') return;
    
        wp_enqueue_style( 'won-style', plugins_url('assets/css/style.css', __FILE__) );
        wp_enqueue_script( 'won-script', plugins_url('assets/js/script.js', __FILE__) , array(), '1.0.0', true );
        
}
add_action( 'admin_enqueue_scripts', 'won_enqueue_scripts' );


function won_enqueue_frontend_scripts($hook) {
    
        wp_enqueue_style( 'won-style', plugins_url('assets/css/frontend-style.css', __FILE__) );
        wp_enqueue_script( 'won-script', plugins_url('assets/js/frontend-script.js', __FILE__) , array(), '1.0.0', true );
        
}
add_action( 'wp_enqueue_scripts', 'won_enqueue_frontend_scripts' )

?>