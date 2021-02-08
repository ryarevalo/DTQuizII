<?php
	class OrderReportController extends AppController{

		public function index(){

			$this->setFlash('Multidimensional Array.');

			$this->loadModel('Order');
			$orders = $this->Order->find('all',array('conditions'=>array('Order.valid'=>1),'recursive'=>2));
			// debug($orders);exit;

			$this->loadModel('Portion');
			$portions = $this->Portion->find('all',array('conditions'=>array('Portion.valid'=>1),'recursive'=>2));
			//debug($portions);exit;

			foreach ($orders as $order) {
				$currentOrder = $order['Order'];
				$currentOrderDetails = $order['OrderDetail'];
				//debug($currentOrder);exit;
				if ($currentOrder['valid'] == true) {
					$temp_sum = array();

					foreach($currentOrderDetails as $detail) {
						if ($detail['valid'] == true) {
							
							$quantity = $detail['quantity'];
							$dishPortion = $portions[$detail['item_id']-1];
							$portionDetails = $dishPortion['PortionDetail'];
							//debug($dishPortion);exit;
							foreach ($portionDetails as $portionDetail) {
								$part = $portionDetail['Part'];
								
								if (!isset($temp_sum[$part['name']])) {
									$temp_sum[$part['name']] = 0;
								}
								
								$temp_sum[$part['name']] += $quantity * $portionDetail['value'];
							}
						}   
					}
	
	
					$order_reports[$order['Order']['name']] = $temp_sum;
				}
	
			}

			// To Do - write your own array in this format
			// $order_reports = array('Order 1' => array(
			// 							'Ingredient A' => 1,
			// 							'Ingredient B' => 12,
			// 							'Ingredient C' => 3,
			// 							'Ingredient G' => 5,
			// 							'Ingredient H' => 24,
			// 							'Ingredient J' => 22,
			// 							'Ingredient F' => 9,
			// 						),
			// 					  'Order 2' => array(
			// 					  		'Ingredient A' => 13,
			// 					  		'Ingredient B' => 2,
			// 					  		'Ingredient G' => 14,
			// 					  		'Ingredient I' => 2,
			// 					  		'Ingredient D' => 6,
			// 					  	),
			// 					);

			// ...

			$this->set('order_reports',$order_reports);

			$this->set('title',__('Orders Report'));
		}

		public function Question(){

			$this->setFlash('Multidimensional Array.');

			$this->loadModel('Order');
			$orders = $this->Order->find('all',array('conditions'=>array('Order.valid'=>1),'recursive'=>2));

			// debug($orders);exit;

			$this->set('orders',$orders);

			$this->loadModel('Portion');
			$portions = $this->Portion->find('all',array('conditions'=>array('Portion.valid'=>1),'recursive'=>2));
				
			// debug($portions);exit;

			$this->set('portions',$portions);

			$this->set('title',__('Question - Orders Report'));
		}

	}