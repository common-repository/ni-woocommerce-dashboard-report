<?php 
if ( ! defined( 'ABSPATH' ) ) { exit;}
if( !class_exists( 'ni_dashboard_report' ) ) {
	class ni_dashboard_report{
		var $is_hpos_enable = false;
		private $months;
		 public function __construct(){
			 add_action('admin_init', array( &$this, 'admin_init'));
			 add_action( 'admin_enqueue_scripts',  array(&$this,'ni_admin_enqueue_scripts' ));
			 //add_filter( 'gettext', array($this, 'get_text'),20,3);
			 $this->is_hpos_enable = $this->is_hpos_enabled();
	
		 }
		 function get_text($translated_text, $text, $domain){
		 	if($domain == 'nidashboardreport'){
		 		return '['.$translated_text.']';
		 	}		
		 return $translated_text;
		 }
		 function is_hpos_enabled() {
            if ( ! class_exists( 'Automattic\\WooCommerce\\Utilities\\OrderUtil' ) ) {
                return false; // OrderUtil class doesn't exist, HPOS is likely not enabled
            }
        
            return Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
		 function admin_init(){
			add_action( 'wp_dashboard_setup', array( &$this, 'ni_add_dashboard_widgets' ));
			
		 }
		 function ni_admin_enqueue_scripts(){
		 	wp_register_style( 'ni-dashboard-report-style', plugins_url( '../assets/css/ni-dashboard-report-style.css', __FILE__ ));
		 	wp_enqueue_style( 'ni-dashboard-report-style' );
		 }
		 function ni_add_dashboard_widgets(){
			wp_add_dashboard_widget(
					 'ni_woocommerce_sales_report_pro',         // Widget slug.
					  __('Ni WooCommerce Sales Reports Pro','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_sales_report_pro_widget')// Display function.
			);	
			wp_add_dashboard_widget(
					 'ni_woocommerce_sales_by_month',         // Widget slug.
					  __('Ni WooCommerce Sales By Month Report','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_sales_by_month')// Display function.
			);	
			
			wp_add_dashboard_widget(
					 'ni_woocommerce_sales_status',         // Widget slug.
					 __('Ni WooCommerce Sales Order Status','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_sales_status_widget')// Display function.
			);	
			
			wp_add_dashboard_widget(
					 'ni_woocommerce_recent_order',         // Widget slug.
					  __('Ni WooCommerce Recent Order','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_recent_order_widget')// Display function.
			);	
			
			wp_add_dashboard_widget(
					 'ni_woocommerce_sales_analysis',         // Widget slug.
					 __('Ni WooCommerce Sales Analysis','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_sales_analysis_widget')// Display function.
			);	
			 wp_add_dashboard_widget(
					 'ni_woocommerce_new_customer',         // Widget slug.
					 __('Ni WooCommerce Customer Report','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_new_customer_widget')// Display function.
			);	
			wp_add_dashboard_widget(
					 'ni_woocommerce_sales_statistics',         // Widget slug.
					 __('Ni WooCommerce Sales Statistics','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_sales_statistics_widget')// Display function.
			);
			
			wp_add_dashboard_widget(
					 'ni_woocommerce_top_sold_product',         // Widget slug.
					 __('Top 10 Sold Products','nidashboardreport'),         // Title.
					  array( &$this, 'ni_woocommerce_top_sold_product_widget')// Display function.
			);
			
			 
			
			
			
			
		 }
		 function ni_woocommerce_top_sold_product_widget(){


		 	  global $wpdb;
			  $query = " SELECT ";
			  $query .= "  SUM(qty.meta_value) as qty ";
			  $query .= " ,ROUND(SUM(line_total.meta_value),2) as line_total ";
			  $query .= " ,line_item.order_item_name as order_item_name ";
				if ( $this->is_hpos_enable){
					$query .= " FROM {$wpdb->prefix}wc_orders as posts ";
				}else{
					$query .= " FROM {$wpdb->prefix}posts as posts ";
				}
			
			  $query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as line_item ON line_item.order_id=posts.ID  " ;
			  
			  $query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as qty ON qty.order_item_id=line_item.order_item_id  ";
			  $query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as line_total ON line_total.order_item_id=line_item.order_item_id  ";
			  
			  $query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as product_id ON product_id.order_item_id=line_item.order_item_id  ";
			  $query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=line_item.order_item_id  ";
			  
			  
			  $query .= " WHERE 1=1 ";
			  if ( $this->is_hpos_enable){
				$query .= " AND posts.type ='shop_order' ";
			  }else{
				$query .= " AND posts.post_type ='shop_order' ";
			  }
			 
			  $query .= " AND qty.meta_key ='_qty' ";
			  $query .= " AND product_id.meta_key ='_product_id' ";
			   $query .= " AND variation_id.meta_key ='_variation_id' ";
			  
			  $query .= " AND line_item.order_item_type ='line_item' ";
			  $query .= " AND line_total.meta_key ='_line_total' ";
			  if ( $this->is_hpos_enable){
				$query .= " AND posts.status IN ('wc-processing','wc-on-hold', 'wc-completed')";
				$query .= " AND  posts.status NOT IN ('trash')";
			  }else{
				$query .= " AND posts.post_status IN ('wc-processing','wc-on-hold', 'wc-completed')";
				$query .= " AND  posts.post_status NOT IN ('trash')";
			  }
			  
			
			  
			  $query .= " GROUP BY product_id.meta_value, variation_id.meta_value  ";
			  $query .= " ORDER BY SUM(qty.meta_value) + 0 DESC ";
			  
			  $query .= " LIMIT 10 ";
			  
			 
				
			 $rows = $wpdb->get_results($query);	
			
			// $this->print_array($rows );
			
			?>
            <div style="overflow-x:auto;">
            <table  class="ni_dashboard_report_table">
            	<thead>
                	<tr>
                    	<th><?php _e("Product Name","nidashboardreport"); ?></th>
                        <th style="text-align:right"><?php _e("Quantity","nidashboardreport"); ?></th>
                        <th style="text-align:right"><?php _e("Product Total","nidashboardreport"); ?></th>
                    </tr>
                </thead>
                <tbody>
                	<?php foreach($rows as $key=>$value): ?>
                    <tr>
                    	<td><?php echo $value->order_item_name; ?></td>
                        <td style="text-align:right"><?php echo $value->qty; ?></td>
                        <td  style="text-align:right"><?php echo wc_price($value->line_total); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php
			
			return $rows ;
		 }
		 function ni_woocommerce_sales_by_month(){
			global $wpdb;
			$order_status =  $this->get_woo_order_status();
			$all_month  = $this->get_months_list();
			 $end_date =date_i18n("Y-m-d");
			
			 $start_date =  date_i18n("Y-m-d", strtotime("-6 months", strtotime($end_date)));
			
			
			$query = "";
			$query .= " SELECT ";
		
			
			//$query .= "  FROM  {$wpdb->prefix}posts as posts ";

			if ( $this->is_hpos_enable){
				$query .= " SUM(total_amount) as order_total";
				$query .= ",  date_format( posts.date_created_gmt, '%Y-%m')   as month";
				$query .= " FROM {$wpdb->prefix}wc_orders as posts ";
			}else{
				$query .= " SUM(order_total.meta_value) as order_total";
				$query .= ",  date_format( posts.post_date, '%Y-%m')   as month";
				$query .= " FROM {$wpdb->prefix}posts as posts ";
				$query  .= " LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID ";
			}

			
			$query .= " WHERE 1=1 ";
			//$query .= " AND posts.post_type = 'shop_order'";

			if ( $this->is_hpos_enable){
				$query .= " AND posts.type ='shop_order' ";
			  }else{
				$query .= " AND posts.post_type ='shop_order' ";
			  }

			  if ( $this->is_hpos_enable){
				//$query .= " AND order_total.meta_key = '_order_total'";
				$query .= " AND   date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
				$query .= " AND posts.status IN ('{$order_status}')	";
				$query .= " GROUP BY YEAR(posts.date_created_gmt), MONTH(posts.date_created_gmt) ";
			  } else{ 
				$query .= " AND order_total.meta_key = '_order_total'";
				$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
				$query .= " AND posts.post_status IN ('{$order_status}')	";
				$query .= " GROUP BY YEAR(posts.post_date), MONTH(posts.post_date) ";
			}



			$row = $wpdb->get_results($query);
			$_net_amount = array();
			foreach($row as $key=>$value){
				$_net_amount[$value->month] = $value->order_total;
			}
			
			//$this->print_array(	$_net_amount);
			
			
			$query = "";
			$query = " SELECT ";
			
			$query .= " SUM(order_itemmeta.meta_value) as order_total";
			if ( $this->is_hpos_enable){
				$query .= ", date_format( posts.date_created_gmt, '%Y-%m')   as month";
			}else{
				$query .= ", date_format( posts.post_date, '%Y-%m')   as month";
			}
		
			if ( $this->is_hpos_enable){
				$query .= "  FROM  {$wpdb->prefix}wc_orders as posts ";
			}else{
				$query .= "  FROM  {$wpdb->prefix}posts as posts ";
			}
			
			
			$query  .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
			
			$query  .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta ON order_itemmeta.order_item_id=order_items.order_item_id ";
			
			$query .= " WHERE 1=1 ";
		
			if ( $this->is_hpos_enable){
				$query .= " AND posts.type ='shop_order' ";
			  }else{
				$query .= " AND posts.post_type ='shop_order' ";
			}


			$query .= " AND order_itemmeta.meta_key = '_line_total'";
			
			if ( $this->is_hpos_enable){
				$query .= " AND   date_format( posts.date_created_gmt, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
				$query .= " AND posts.status IN ('{$order_status}')	";
				$query .= " GROUP BY YEAR(posts.date_created_gmt), MONTH(posts.date_created_gmt) ";
			}else{
				$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
				$query .= " AND posts.post_status IN ('{$order_status}')	";
				$query .= " GROUP BY YEAR(posts.post_date), MONTH(posts.post_date) ";
			}
			
		
			$row = $wpdb->get_results($query);
			$_gross_amount = array();
			foreach($row as $key=>$value){
				$_gross_amount[$value->month] = $value->order_total;
			}
			
			foreach($all_month as $key=>$value){
				$gross_amount[$key]["Gross"] = isset($_gross_amount[$key])?$_gross_amount[$key]:0;
				$gross_amount[$key]["Net"] = isset($_net_amount[$key])?$_net_amount[$key]:0;
				$gross_amount[$key]["Month"] = $value;
			}
			//$this->print_array(	$gross_amount);
		$gross_amount = 	array_reverse ($gross_amount);
			?>
			<div style="overflow-x:auto;">
            <table  class="ni_dashboard_report_table">
            	<thead>
                	<tr>
                        <th><?php _e("Month Name","nidashboardreport") ?></th>
                        <th style="text-align:right"><?php _e("Total Gross Sales","nidashboardreport"); ?></th>
                        <th style="text-align:right"><?php _e("Total Net Sales","nidashboardreport"); ?> </th>
                	</tr>
                </thead>
            <?php
			foreach($gross_amount as $key=>$value){
			?>
            <tbody>
            	<tr>
            		<td style="font-weight:bold"><?php printf( esc_html__( '%s', 'nidashboardreport' ),$value["Month"] ); ?></td>
                	<td style="text-align:right"><?php echo wc_price(isset($value["Gross"])?$value["Gross"]:0); ?></td>
                	<td style="text-align:right"><?php echo wc_price(isset($value["Net"])?$value["Net"]:0); ?></td>
            	</tr>
            </tbody>
            <?php		
			}
			?>
           	</table>
			</div>	
            <?php
			
		 }
		 function ni_woocommerce_sales_report_pro_widget(){
		 ?>
			<div style="overflow-x:auto;">
			<table class="ni_dashboard_report_table">
				<tr>
					<td colspan="2" style="font-weight:bold; color:#2cc185"><?php _e("Buy Ni WooCommerce Sales Report Pro @ $24.00","nidashboardreport"); ?></td>
				</tr>
				<tr>
					<td>
						<ul>
							<li><?php _e("Dashboard order Summary","nidashboardreport"); ?></li>
							<li><?php _e("Order List - Display order list","nidashboardreport"); ?></li>
							<li><?php _e("Order Detail - Display Product information","nidashboardreport"); ?></li>
							<li><?php _e("Customer Sales Report","nidashboardreport"); ?></li>
						</ul>
					</td>
					<td>
						<ul>
							<li><?php _e("Payment Gateway Sales Report","nidashboardreport"); ?></li>
							<li><?php _e("Country Sales Report","nidashboardreport"); ?></li>
							<li><?php _e("Coupon Sales Report","nidashboardreport"); ?></li>
							<li><?php _e("Order Status Sales Report","nidashboardreport"); ?></li>
						</ul>
					</td>
				</tr>
				<tr>
					<td>
						<ul>
							<li><span style="color:#26A69A"><?php _e("Email at","nidashboardreport"); ?>: <a href="mailto:support@naziinfotech.com">support@naziinfotech.com</a></span></li>						 <li><?php _e("Coupon Code","nidashboardreport"); ?>: <span style="color:#26A69A">ni10</span><?php _e("Get 10% OFF","nidashboardreport"); ?></li>
						</ul>
						</td>
					<td>
						<ul>
							 <li><a href="http://demo.naziinfotech.com/?demo_login=woo_sales_report" target="_blank"><?php _e("View Demo","nidashboardreport"); ?></a>  </li>
							<li><a href="http://naziinfotech.com/?product=ni-woocommerce-sales-report-pro" target="_blank"><?php _e("Buy Now","nidashboardreport"); ?></a>  </li>
						</ul>
					</td>
				</tr>
			</table>
			</div>	
		 <?php
		 }
		 function ni_woocommerce_sales_analysis_widget(){
			$order_data  = array();
		
			

			 if ( $this->is_hpos_enable){
				$order_data = $this->get_sales_analysis_hpos();
			 }else{
				$order_data = $this->get_sales_analysis();
			 }
			 
			 ?>
			<div style="overflow-x:auto;">	
			 <table class="ni_dashboard_report_table">
				<thead>
                	<tr>
						<th style="text-align:left;" ><?php _e("Order Interval","nidashboardreport"); ?></th>
						<th style="text-align:right"><?php _e("Order Count","nidashboardreport"); ?></th>
						<th style="text-align:right"><?php _e("Order Total","nidashboardreport"); ?></th>
					</tr>
                </thead>
                <?php foreach($order_data as $key=>$value) { ?>
				<tr>
                	<td style="font-weight:bold" ><?php printf( esc_html__( '%s', 'nidashboardreport' ),$value->order_day ); ?></td>
					<td style="text-align:right"><?php echo isset( $value->order_count)? $value->order_count:0;  ?></td>
					<td style="text-align:right"><?php echo wc_price( isset($value->total_sales)?$value->total_sales:0);  ?></td>
				</tr>
				<?php } ?>
			 </table>
			 </div>	
			 <?php
		 }
		 function ni_woocommerce_sales_status_widget() {
	
			// Display whatever it is you want to show.
			//echo "Hello World, I'm a great Dashboard Widget Anzar Ahmed";
			$this->get_order_status();
			
		}
		function ni_woocommerce_recent_order_widget(){
			$order_data = $this->get_recent_orders();
			//$this->print_array($order_data);	
			?>
			<div style="overflow-x:auto;">
			<table class="ni_dashboard_report_table">
				<thead>
            		<tr>
                        <th style="text-align:left"><?php _e("ID","nidashboardreport"); ?></th>
                        <th style="text-align:left"><?php _e("Date","nidashboardreport"); ?> </th>
                        <th style="text-align:left"><?php _e("First Name","nidashboardreport"); ?></th>
                        <th style="text-align:left"><?php _e("Billing Email","nidashboardreport"); ?></th>
                        <th style="text-align:left"><?php _e("Status","nidashboardreport"); ?></th>
                        <th style="text-align:left"><?php _e("Order Total","nidashboardreport"); ?></th>
                    </tr>
            	</thead>
			<?php foreach ($order_data as $k=>$v): ?>	
			<tbody>
            	<tr> 
				 <td><?php echo $v->order_id; ?></td>
				
				 <td><?php echo $v->order_date; ?></td>
				
				 <td><?php echo isset($v->billing_first_name)?$v->billing_first_name:""; ?></td>
				 <td><?php echo isset($v->billing_email)?$v->billing_email:""; ?></td>
				
				<td><?php echo ucfirst (str_replace("wc-","", $v->order_status)); ?></td>
				<td style="text-align:right"><?php echo wc_price(isset($v->order_total)?$v->order_total:0); ?></td>
			
			 </tr>
            </tbody>
			 <?php endforeach; ?>
		</table></div>
			<?php
		}
		function get_recent_orders(){
			global $wpdb;

			$query = "";
			
			$query .= " SELECT ";
			
		
			if ( $this->is_hpos_enable){
				$query .= "		posts.ID as order_id";
				$query .= "		,posts.status as order_status";
				$query .= "		, date_format( posts.date_created_gmt, '%Y-%m-%d') as order_date 	";
				$query .= "		FROM {$wpdb->prefix}wc_orders as posts	";
			}else{
				$query .= "		posts.ID as order_id";
				$query .= "		,posts.post_status as order_status";
				$query .= "		, date_format( posts.post_date, '%Y-%m-%d') as order_date 	";
				$query .= "		FROM {$wpdb->prefix}posts as posts	";
			}
		
					
					$query .= " WHERE 1=1  ";
					if ( $this->is_hpos_enable){
						$query .= " AND  posts.type = 'shop_order'";
						$query .= " AND  posts.status NOT IN ('auto-draft')";
						$query .= "order by posts.date_created_gmt DESC";	
					}else{
						$query .= " AND  posts.post_type = 'shop_order'";
						$query .= " AND  posts.post_status NOT IN ('auto-draft')";
						$query .= "order by posts.post_date DESC";	
					}
					
					
				
					$query .= " LIMIT 5";	
					$order_data = $wpdb->get_results( $query);	
					//$this->print_array($order_data);				
					if(count($order_data)> 0){
						foreach($order_data as $k => $v){
							
							/*Order Data*/
							$order_id =$v->order_id;
							$order_detail = $this->get_order_post_meta($order_id);
							foreach($order_detail as $dkey => $dvalue)
							{
									$order_data[$k]->$dkey =$dvalue;
								
							}
						}
						
						return $order_data;
					}
					else
					{
						  _e("No Record Found","nidashboardreport");
					}
									
					
					
		}
		function get_woo_order_status(){
		//$order_status = array('wc-pending','wc-processing','wc-on-hold', 'wc-completed','wc-refunded');
		$order_status =  implode("','", array('wc-processing','wc-on-hold', 'wc-completed','wc-refunded'));
		
		return $order_status;
		}
		function get_sales_analysis(){
			$query = "";
			$order_status =  $this->get_woo_order_status();
			
			$today = date_i18n("Y-m-d");
			global $wpdb;	
			$query = "";
			$query .= " SELECT ";
			$query .= "			SUM(order_total.meta_value)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'Today' as 'order_day'";
			$query .= "			,'#AD1457' as 'color'";
			$query .= "			FROM {$wpdb->prefix}posts as posts";			
			$query .= "			LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID ";
						
			$query .= "			WHERE 1=1";
			$query .= "			AND posts.post_type ='shop_order' ";
			$query .= "			AND order_total.meta_key='_order_total' ";
				
			$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') = date_format('{$today}', '%Y-%m-%d')"; 
			$query .= " AND posts.post_status IN ('{$order_status}')	";
				
			$query .= " UNION ALL ";
				
				/*Yesterday*/
			$query .= "SELECT ";
			$query .= "			SUM(order_total.meta_value)as 'total_sales' ";
			$query .= "			,count(*) as order_count ";
			$query .= "			,'Yesterday' as 'order_day' ";
			$query .= "			,'#6A1B9A' as 'color' ";
			$query .= "	FROM {$wpdb->prefix}posts as posts	 ";		
			$query .= "			LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID  ";
						
			$query .= "			WHERE 1=1 ";
			$query .= "			AND posts.post_type ='shop_order'  ";
			$query .= "			AND order_total.meta_key='_order_total' ";
			$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') = DATE_SUB(date_format('{$today}' , '%Y-%m-%d'), INTERVAL 1 DAY) "; 
			$query .= " AND posts.post_status IN ('{$order_status}')	";
				
			$query .= " UNION ALL ";
				
				/*Week*/
			$query .= "SELECT ";
			$query .= "			SUM(order_total.meta_value)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'This Week' as 'order_day'";
			$query .= "			,'#6A1B9A' as 'color'";
			$query .= "			FROM {$wpdb->prefix}posts as posts		";	
			$query .= "			LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID ";
						
			$query .= "			WHERE 1=1";
			$query .= "			AND posts.post_type ='shop_order' ";
			$query .= "			AND order_total.meta_key='_order_total' ";
			$query .= "  AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND ";
		 	$query .= " WEEK(date_format( posts.post_date, '%Y-%m-%d')) = WEEK(CURRENT_DATE()) ";
			$query .= " AND posts.post_status IN ('{$order_status}')	";
			$query .= " UNION ALL ";
				/*Month*/
			$query .= " SELECT ";
			$query .= "			SUM(order_total.meta_value)as 'total_sales' ";
			$query .= "			,count(*) as order_count ";
			$query .= "			,'This Month' as 'order_day' ";
			$query .= "			,'#1565C0' as 'color' ";
			$query .= "			FROM {$wpdb->prefix}posts as posts	 ";		
			$query .= "			LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID  ";
						
			$query .= "			WHERE 1=1 ";
			$query .= "			AND posts.post_type ='shop_order'  ";
			$query .= "			AND order_total.meta_key='_order_total' ";
			$query .= "  AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND  ";
			$query .= " MONTH(date_format( posts.post_date, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
			$query .= " AND posts.post_status IN ('{$order_status}')	";

			$query .= " UNION ALL ";
				/*Year*/
			$query .= " SELECT ";
			$query .= "			SUM(order_total.meta_value)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'This Year' as 'order_day'";
			$query .= "			,'#FF5722' as 'color'";
			$query .= "			FROM {$wpdb->prefix}posts as posts		";	
			$query .= "			LEFT JOIN  {$wpdb->prefix}postmeta as order_total ON order_total.post_id=posts.ID ";
						
			$query .= "			WHERE 1=1";
			$query .= "			AND posts.post_type ='shop_order' ";
			$query .= "			AND order_total.meta_key='_order_total' ";
				
			$query .= " AND YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(NOW(), '%Y-%m-%d')) "; 
				
			$query .= " AND posts.post_status IN ('{$order_status}')	";
			
			$order_data = $wpdb->get_results( $query);	
			
			return $order_data;
		}
		function get_sales_analysis_hpos(){
			$query = "";
			$order_status =  $this->get_woo_order_status();
			
			$today = date_i18n("Y-m-d");
			global $wpdb;	
			$query = "";
			$query .= " SELECT ";
			$query .= "			SUM(total_amount)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'Today' as 'order_day'";
			$query .= "			,'#AD1457' as 'color'";
			$query .= "			FROM {$wpdb->prefix}wc_orders as posts";			
					
			$query .= "			WHERE 1=1";
			$query .= "			AND posts.type ='shop_order' ";
		
			$query .= " AND   date_format( posts.date_created_gmt, '%Y-%m-%d') = date_format('{$today}', '%Y-%m-%d')"; 
			$query .= " AND posts.status IN ('{$order_status}')	";
				
			$query .= " UNION ALL ";
				
				/*Yesterday*/
			$query .= "SELECT ";
			$query .= "			SUM(total_amount)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'Yesterday' as 'order_day' ";
			$query .= "			,'#AD1457' as 'color'";
			$query .= "			FROM {$wpdb->prefix}wc_orders as posts";
			
						
			$query .= "			WHERE 1=1 ";
			$query .= "			AND posts.type ='shop_order' ";
		
			$query .= " AND   date_format( posts.date_created_gmt, '%Y-%m-%d') = DATE_SUB(date_format('{$today}' , '%Y-%m-%d'), INTERVAL 1 DAY) "; 
			$query .= " AND posts.status IN ('{$order_status}')	";
				
			$query .= " UNION ALL ";
				
				/*Week*/
			$query .= "SELECT ";
			$query .= "			SUM(total_amount)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'This Week' as 'order_day'";
			$query .= "			,'#AD1457' as 'color'";
			$query .= "			FROM {$wpdb->prefix}wc_orders as posts";
			
			$query .= "			WHERE 1=1";
			$query .= "			AND posts.type ='shop_order' ";
		
			$query .= "  AND  YEAR(date_format( posts.date_created_gmt, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND ";
		 	$query .= " WEEK(date_format( posts.date_created_gmt, '%Y-%m-%d')) = WEEK(CURRENT_DATE()) ";
			$query .= " AND posts.status IN ('{$order_status}')	";
			$query .= " UNION ALL ";
				/*Month*/
			$query .= " SELECT ";
			$query .= "			SUM(total_amount)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'This Month' as 'order_day' ";
			$query .= "			,'#AD1457' as 'color'";
			$query .= "			FROM {$wpdb->prefix}wc_orders as posts";
		
			$query .= "			WHERE 1=1 ";
			$query .= "			AND posts.type ='shop_order'  ";
			$query .= "  AND  YEAR(date_format( posts.date_created_gmt, '%Y-%m-%d')) = YEAR(CURRENT_DATE()) AND  ";
			$query .= " MONTH(date_format( posts.date_created_gmt, '%Y-%m-%d')) = MONTH(CURRENT_DATE()) ";
			$query .= " AND posts.status IN ('{$order_status}')	";

			$query .= " UNION ALL ";
				/*Year*/
			$query .= " SELECT ";
			$query .= "			SUM(total_amount)as 'total_sales'";
			$query .= "			,count(*) as order_count";
			$query .= "			,'This Year' as 'order_day'";
			$query .= "			,'#AD1457' as 'color'";
			$query .= "			FROM {$wpdb->prefix}wc_orders as posts";
						
			$query .= "			WHERE 1=1";
			$query .= "			AND posts.type ='shop_order' ";
			
				
			$query .= " AND YEAR(date_format( posts.date_created_gmt, '%Y-%m-%d')) = YEAR(date_format(NOW(), '%Y-%m-%d')) "; 
				
			$query .= " AND posts.status IN ('{$order_status}')	";
			
			$order_data = $wpdb->get_results( $query);	
			
			return $order_data;
		}
		function get_order_status(){
			global $wpdb;
			$query = "SELECT ";
			if ($this->is_hpos_enable){
				$query .= "  post.status as order_status";
				$query .= " , SUM(total_amount) as order_total";
				$query .= " , COUNT(*) as order_count";
				$query .= " FROM {$wpdb->prefix}wc_orders as post  ";
				
			
				
				$query .= " WHERE 1=1  ";
				$query .= " AND  post.type = 'shop_order'";
				
				
				$query .= " GROUP BY post.status ";
				$query .= " ORDER  BY SUM(total_amount) DESC";
			}else{
				$query .= "  post.post_status as order_status";
				$query .= " , SUM(postmeta.meta_value) as order_total";
				$query .= " , COUNT(*) as order_count";
				$query .= " FROM {$wpdb->prefix}posts as post  ";
				
				$query .= "LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=post.ID ";
				
				$query .= " WHERE 1=1  ";
				$query .= " AND  post.post_type = 'shop_order'";
				$query .= " AND  postmeta.meta_key = '_order_total'";
				
				$query .= " GROUP BY post.post_status ";
				$query .= " ORDER  BY  SUM(postmeta.meta_value) DESC";
			}
			
			
			$results = $wpdb->get_results( $query);	
			
		?>
		<style>
		
		</style>
		<table class="ni_dashboard_report_table">
			<thead>
            	<tr>
                    <th style="text-align:left"><?php   _e("Order Status","nidashboardreport"); ?></th>
                    <th style="text-align:left"><?php   _e("Count","nidashboardreport"); ?></th>
                    <th style="text-align:left"><?php   _e("Total","nidashboardreport"); ?></th>
                    <th style="text-align:left"><?php   _e("Action","nidashboardreport"); ?></th>
				</tr>
            </thead>
			<?php foreach ($results as $k=>$v): ?>	
			<tr> 
				<td><?php printf( esc_html__( '%s', 'nidashboardreport' ),ucfirst (str_replace("wc-","", $v->order_status)) ); ?></td>
				<td><?php echo $v->order_count; ?></td>
				<td><?php echo wc_price($v->order_total); ?></td>
				<td><a href="<?php echo admin_url()."edit.php?post_type=shop_order&post_status=".$v->order_status ?>"><?php _e("view","nidashboardreport"); ?></a></td>
			 </tr>
			 <?php endforeach; ?>
		</table>
		<?php
		}
		function print_array($ar = NULL,$display = true){
				if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
				}
		}
		function get_order_post_meta($order_id)
		{
			$order_detail	= get_post_meta($order_id);
			$order_detail_array = array();
			foreach($order_detail as $k => $v)
			{
				$k =substr($k,1);
				$order_detail_array[$k] =$v[0];
			}
			return 	$order_detail_array;
		}
		function get_months_list($amount_column = true){
			
			$cross_tab_end_date			=  date_i18n("Y-m-d");
			$cross_tab_start_date		=  date_i18n("Y-m-d", strtotime("-6 months", strtotime($cross_tab_end_date)));
			
			$startDate = strtotime($cross_tab_start_date);
			$endDate   = strtotime($cross_tab_end_date);
			$currentDate = $startDate;
			$this->months = array();
			if($amount_column){					
			
				while ($currentDate <= $endDate) {
					
					$month = date('Y-m',$currentDate);
					$this->months[$month] = date('F',$currentDate);
					$currentDate = strtotime( date('Y/m/01/',$currentDate).' 1 month');
				}
			}else{
				while ($currentDate <= $endDate) {
					$month = date('Y-m',$currentDate);
					$this->months[$month."_total"] = date('M',$currentDate)." Amt.";
					$this->months[$month."_quantity"] = date('M',$currentDate)." Qty.";
					$currentDate = strtotime( date('Y/m/01/',$currentDate).' 1 month');
				}
			}
				
			
			//$this->print_array(	$this->months);
			return $this->months;
		}
		function ni_woocommerce_sales_statistics_widget(){
			global $wpdb;	
			$query = "";
			$query .= " SELECT ";
			if ( $this->is_hpos_enable){
				$query .= "max(posts.date_created_gmt) as order_date ";

				$query .= "FROM {$wpdb->prefix}wc_orders as posts	";
	
				$query .= " WHERE 1=1  ";
				$query .= " AND  posts.type = 'shop_order'";
				$query .= " AND  posts.status NOT IN ('auto-draft')";
	
				$query .= "order by posts.date_created_gmt DESC";	

			}else{
				$query .= "max(posts.post_date) as order_date ";

				$query .= "FROM {$wpdb->prefix}posts as posts	";
	
				$query .= " WHERE 1=1  ";
				$query .= " AND  posts.post_type = 'shop_order'";
				$query .= " AND  posts.post_status NOT IN ('auto-draft')";
	
				$query .= "order by posts.post_date DESC";	
			}
		
		
			$results = $wpdb->get_var( $query);	
			$this->time_elapsed_string($results);
			//$this->print_array();
			?>
			<table class="ni_dashboard_report_table1">
				<tr>
					<td style="font-size:16px; color: #6A1B9A; text-transform: uppercase; font-weight: bold"> <?php   _e("Last Order","nidashboardreport"); ?></td>
				</tr>
				<tr>
					<td> <span style="font-size:24px; color: #CE93D8"><?php echo $this->time_elapsed_string($results);  ?></span> <?php echo  ""."  (".  $results .") "  ;?> </td>
				</tr>
			</table>
			<?php
		}
		function time_elapsed_string($datetime, $full = false) {
			$now = new DateTime();
			$ago = new DateTime($datetime);
			$diff = $now->diff($ago);
		
			// Calculate the number of weeks and adjust days
			$weeks = floor($diff->days / 7);
			$days = $diff->days % 7;
		
			$string = array(
				'y' => 'year',
				'm' => 'month',
				'w' => 'week',
				'd' => 'day',
				'h' => 'hour',
				'i' => 'minute',
				's' => 'second',
			);
		
			// Create an associative array to hold the time components
			$result = array(
				'y' => $diff->y,
				'm' => $diff->m,
				'w' => $weeks,
				'd' => $days,
				'h' => $diff->h,
				'i' => $diff->i,
				's' => $diff->s,
			);
		
			// Build the result string
			$parts = array();
			foreach ($string as $k => $v) {
				if ($result[$k]) {
					$parts[] = $result[$k] . ' ' . $v . ($result[$k] > 1 ? 's' : '');
				}
			}
		
			if (!$full) $parts = array_slice($parts, 0, 1);
			return $parts ? implode(', ', $parts) . ' ago' : 'just now';
		}
		
		function ni_woocommerce_new_customer_widget(){
			$lists['today']['count'] 		=  $daily_count = $this->new_customer('today');
			$lists['current_week']['count']	=  $daily_count = $this->new_customer('current_week');
			$lists['current_month']['count']=  $daily_count = $this->new_customer('current_month');
			$lists['current_year']['count']	=  $daily_count = $this->new_customer('current_year');
			
			$lists['today']['label'] 		=  __('Today ');
			$lists['current_week']['label']	=  __('Current Week ');
			$lists['current_month']['label']=  __('Current Month ');
			$lists['current_year']['label']	=  __('Current Year ');
			
			//$this->print_list($lists);
						
			$output = "<table class=\"widefat fixed\" cellspacing=\"0\">";
			
			
			
			$output .= "<thead>";
			$output .= "<tr>";
				
				
				$output .= "<th>";
					$output .= __('<b>New Customers</b>');
				$output .= "</th>";
					
				$output .= "<th style=\"text-align:right\">";
					$output .= __('<b>Total</b>');
				$output .= "</th>";
				
				
				$output .= "</tr>";
			$output .= "</thead>";
			$output .= "<tbody>";
			
			foreach ($lists as $key => $list){
				$output .= "<tr>";
				
				
				$output .= "<td style=\"color: #6A1B9A;font-size: 14px;\">";
					$output .= $list['label'];
				$output .= "</td>";
					
				$output .= "<td style=\"text-align:right; color: #6A1B9A;font-weight: bold; font-size:16px;\">";
					$output .= $list['count'];
				$output .= "</td>";
				
				
				$output .= "</tr>";
			}
			$output .= "</tbody>";
			$output .= "</table>";
			
			$output .= '<div style="height: auto; text-align: center; margin-top:5px;"><a title="naziinfotech" href="http://naziinfotech.com/" TARGET="_blank">
                                                    <img alt="naziinfotech" style="width:200px" src="http://naziinfotech.com/wp-content/uploads/2018/04/cropped-logo.png">
                                            </a></div>';
			
			echo $output;
		}
		function new_customer($type = 'daily'){
			global $wpdb;
			
			$sql = " SELECT ";
			$sql .= " billing_email.meta_value AS billing_email";			
			$sql .= " FROM $wpdb->posts AS shop_order";
			$sql .= " LEFT JOIN $wpdb->postmeta AS billing_email ON billing_email.post_id = shop_order.ID";
			$sql .= " WHERE 1*1";
			$sql .= " AND shop_order.post_type = 'shop_order'";
			$sql .= " AND billing_email.meta_key = '_billing_email'";
			
			switch($type){
				case "today":
					$start_date = date_i18n("Y-m-d");
					
					$sql .= " AND  date_format( shop_order.post_date, '%Y-%m-%d') = '{$start_date}'";
					
					$billing_emails = $this->get_old_customer_billing_email($type,$start_date);
					
					if($billing_emails)
						$sql .= " AND   billing_email.meta_value NOT IN ($billing_emails)";
					break;
				case "current_week":

					$week_days 			= array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
					$today 				= date_i18n("Y-m-d");
					$today_string 		= strtotime($today);
					$curren_week_day 	= date("w",$today_string);					
					$start_of_week 		= get_option('start_of_week');
					
					$start_of_week_day = $week_days[$start_of_week];
					
					if($curren_week_day == $start_of_week){
						$start_date =$today;
					}else{
						$start_date 	= date("Y-m-d",strtotime('last '.$start_of_week_day,$today_string));
					}
					$sql .= " AND date_format( shop_order.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$today}'";
					$billing_emails = $this->get_old_customer_billing_email($type,$start_date);
					
					if($billing_emails)
						$sql .= " AND   billing_email.meta_value NOT IN ($billing_emails)";
					break;
				case "current_month":

					$today 				= date_i18n("Y-m-d");
					$today_string 		= strtotime($today);
					$start_date			= date_i18n("Y-m-01",$today_string);
					
					
					$sql .= " AND date_format( shop_order.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$today}'";
					$billing_emails = $this->get_old_customer_billing_email($type,$start_date);
					
					if($billing_emails)
						$sql .= " AND   billing_email.meta_value NOT IN ($billing_emails)";
					break;
				case "current_year":

					$today 				= date_i18n("Y-m-d");
					$today_string 		= strtotime($today);
					$start_date			= date_i18n("Y-01-01",$today_string);
					
					
					$sql .= " AND date_format( shop_order.post_date, '%Y-%m-%d') BETWEEN  '{$start_date}' AND '{$today}'";
					
					 $billing_emails = $this->get_old_customer_billing_email($type,$start_date);
					
					if($billing_emails)
						$sql .= " AND   billing_email.meta_value NOT IN ($billing_emails)";
					break;
				default:
					break;
			}
			
			//echo $type;
			//echo "<br>";
			$sql .= " GROUP BY billing_email";
			
			
			$item = $wpdb->get_results($sql);
			return count($item);
			
		}
		function get_old_customer_billing_email($type = 'daily', $start_date = ''){
			global $wpdb;
			
			$sql = " SELECT ";			
			$sql .= " billing_email.meta_value AS billing_email";			
			$sql .= " FROM $wpdb->posts AS shop_order";
			$sql .= " LEFT JOIN $wpdb->postmeta AS billing_email ON billing_email.post_id = shop_order.ID";
			$sql .= " WHERE 1*1";
			$sql .= " AND shop_order.post_type = 'shop_order'";
			$sql .= " AND billing_email.meta_key = '_billing_email'";
			
			switch($type){
				case "today":
				case "current_week":
				case "current_month":
				case "current_year":
					$sql .= " AND  date_format( shop_order.post_date, '%Y-%m-%d') < '{$start_date}'";
					break;
				default:
					break;
			}
			
			$sql .= " GROUP BY billing_email";
			
			$item = $wpdb->get_results($sql);
			
			if($item){
				$emails  = $this->get_items_id_list($item,'billing_email');
			}else{
				$emails  = '';
			}
			
			return $emails;
			
		}
		function get_items_id_list($order_items = array(),$field_key = 'order_id', $return_default = '-1' , $return_formate = 'string'){
			$list 	= array();
			$string = $return_default;
			if(count($order_items) > 0){
				foreach ($order_items as $key => $order_item) {
					if(isset($order_item->$field_key))
						$list[] = $order_item->$field_key;
				}
				
				$list = array_unique($list);
				
				if($return_formate == "string"){
					$string = "'".implode("', '",$list)."'";
				}else{
					$string = $list;
				}
			}
			return $string;
		}

	}
}