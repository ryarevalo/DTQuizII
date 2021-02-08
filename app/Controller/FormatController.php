<?php
	class FormatController extends AppController{
		
		public function q1(){
			
			$this->setFlash('Question: Please change Pop Up to mouse over (soft click)');
				
			$this->set('tooltip1', '<span style=\'display:inline-block\'>
							<ul>
								<li>Type 1</li>
							<li>Description 1</li>
							</ul>
						</span>');

            $this->set('tooltip2', '<span style=\'display:inline-block\'>
							<ul>
								<li>Type 2</li>
							<li>Description 2</li>
							</ul>
						</span>');

			$this->set('tooltip3', '<span style=\'display:inline-block\'>
						<ul>
							<li>Type 3</li>
							<li>Description 3</li>
						</ul>
					</span>');

			if ($this->request->is('POST')){
				$this->set('selectedType', $this->request->data['Type']['type']);
			}		
// 			$this->set('title',__('Question: Please change Pop Up to mouse over (soft click)'));
		}
		
		public function q1_detail(){

			$this->setFlash('Question: Please change Pop Up to mouse over (soft click)');
				
			
			
// 			$this->set('title',__('Question: Please change Pop Up to mouse over (soft click)'));
		}
		
	}